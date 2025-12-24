<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Repository;

use Modules\Order\Domain\Model\Order;
use Modules\Order\Domain\Model\OrderAddress;
use Modules\Order\Domain\Model\OrderItem;
use Shared\Infrastructure\Database\DatabaseConnection;

class OrderRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function save(Order $order): void
    {
        if ($order->getId() === null) {
            $this->insert($order);
        } else {
            $this->update($order);
        }
    }

    private function insert(Order $order): void
    {
        $this->db->beginTransaction();
        try {
            // Insert order
            $stmt = $this->db->prepare("
                INSERT INTO orders (
                    order_number, customer_id, subtotal, shipping_fee, discount, total,
                    status, payment_status, payment_method, payment_transaction_id,
                    shipping_full_name, shipping_phone, shipping_email, shipping_address,
                    shipping_ward, shipping_district, shipping_province, shipping_postal_code,
                    billing_full_name, billing_phone, billing_email, billing_address,
                    billing_ward, billing_district, billing_province, billing_postal_code,
                    notes, created_at, updated_at
                ) VALUES (
                    :order_number, :customer_id, :subtotal, :shipping_fee, :discount, :total,
                    :status, :payment_status, :payment_method, :payment_transaction_id,
                    :shipping_full_name, :shipping_phone, :shipping_email, :shipping_address,
                    :shipping_ward, :shipping_district, :shipping_province, :shipping_postal_code,
                    :billing_full_name, :billing_phone, :billing_email, :billing_address,
                    :billing_ward, :billing_district, :billing_province, :billing_postal_code,
                    :notes, :created_at, :updated_at
                )
            ");

            $shipping = $order->getShippingAddress();
            $billing = $order->getBillingAddress();

            $stmt->execute([
                ':order_number' => $order->getOrderNumber(),
                ':customer_id' => $order->getCustomerId(),
                ':subtotal' => $order->getSubtotal(),
                ':shipping_fee' => $order->getShippingFee(),
                ':discount' => $order->getDiscount(),
                ':total' => $order->getTotal(),
                ':status' => $order->getStatus(),
                ':payment_status' => $order->getPaymentStatus(),
                ':payment_method' => $order->getPaymentMethod(),
                ':payment_transaction_id' => $order->getPaymentTransactionId(),
                ':shipping_full_name' => $shipping->getFullName(),
                ':shipping_phone' => $shipping->getPhone(),
                ':shipping_email' => $shipping->getEmail(),
                ':shipping_address' => $shipping->getAddress(),
                ':shipping_ward' => $shipping->getWard(),
                ':shipping_district' => $shipping->getDistrict(),
                ':shipping_province' => $shipping->getProvince(),
                ':shipping_postal_code' => $shipping->getPostalCode(),
                ':billing_full_name' => $billing->getFullName(),
                ':billing_phone' => $billing->getPhone(),
                ':billing_email' => $billing->getEmail(),
                ':billing_address' => $billing->getAddress(),
                ':billing_ward' => $billing->getWard(),
                ':billing_district' => $billing->getDistrict(),
                ':billing_province' => $billing->getProvince(),
                ':billing_postal_code' => $billing->getPostalCode(),
                ':notes' => $order->getNotes(),
                ':created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
                ':updated_at' => $order->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]);

            $orderId = (int)$this->db->lastInsertId();
            $order->setId($orderId);

            // Insert order items
            foreach ($order->getItems() as $item) {
                $itemStmt = $this->db->prepare("
                    INSERT INTO order_items (
                        order_id, product_id, product_name, product_sku,
                        quantity, unit_price, total_price
                    ) VALUES (
                        :order_id, :product_id, :product_name, :product_sku,
                        :quantity, :unit_price, :total_price
                    )
                ");

                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item->getProductId(),
                    ':product_name' => $item->getProductName(),
                    ':product_sku' => $item->getProductSku(),
                    ':quantity' => $item->getQuantity(),
                    ':unit_price' => $item->getUnitPrice(),
                    ':total_price' => $item->getTotalPrice(),
                ]);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function update(Order $order): void
    {
        $stmt = $this->db->prepare("
            UPDATE orders SET
                status = :status,
                payment_status = :payment_status,
                payment_transaction_id = :payment_transaction_id,
                updated_at = :updated_at
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $order->getId(),
            ':status' => $order->getStatus(),
            ':payment_status' => $order->getPaymentStatus(),
            ':payment_transaction_id' => $order->getPaymentTransactionId(),
            ':updated_at' => $order->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(int $id): ?Order
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE order_number = :order_number");
        $stmt->execute([':order_number' => $orderNumber]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    private function mapToEntity(array $data): Order
    {
        $shippingAddress = new OrderAddress(
            $data['shipping_full_name'],
            $data['shipping_phone'],
            $data['shipping_email'],
            $data['shipping_address'],
            $data['shipping_ward'],
            $data['shipping_district'],
            $data['shipping_province'],
            $data['shipping_postal_code']
        );

        $billingAddress = new OrderAddress(
            $data['billing_full_name'],
            $data['billing_phone'],
            $data['billing_email'],
            $data['billing_address'],
            $data['billing_ward'],
            $data['billing_district'],
            $data['billing_province'],
            $data['billing_postal_code']
        );

        // Load order items
        $itemsStmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $itemsStmt->execute([':order_id' => $data['id']]);
        $items = [];

        while ($itemData = $itemsStmt->fetch()) {
            $items[] = new OrderItem(
                $itemData['product_id'],
                $itemData['product_name'],
                $itemData['product_sku'],
                $itemData['quantity'],
                (float)$itemData['unit_price']
            );
        }

        $order = new Order(
            $data['order_number'],
            $data['customer_id'],
            $items,
            (float)$data['subtotal'],
            (float)$data['total'],
            $data['payment_method'],
            $shippingAddress,
            $billingAddress
        );

        $order->setId($data['id']);
        $order->setShippingFee((float)$data['shipping_fee']);
        $order->setDiscount((float)$data['discount']);
        $order->setStatus($data['status']);
        $order->setPaymentStatus($data['payment_status']);
        $order->setPaymentTransactionId($data['payment_transaction_id']);
        $order->setNotes($data['notes']);

        return $order;
    }

    public function findByCustomerId(int $customerId, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM orders 
            WHERE customer_id = :customer_id 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':customer_id', $customerId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $orders = [];
        while ($data = $stmt->fetch()) {
            $orders[] = $this->mapToEntity($data);
        }

        return $orders;
    }

    public function findAll(int $limit = 100, int $offset = 0, ?string $status = null): array
    {
        $sql = "SELECT * FROM orders";
        $params = [];

        if ($status !== null) {
            $sql .= " WHERE status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();

        $orders = [];
        while ($data = $stmt->fetch()) {
            $orders[] = $this->mapToEntity($data);
        }

        return $orders;
    }
}

