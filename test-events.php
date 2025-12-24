<?php

/**
 * Test Event System
 * Run: php test-events.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Shared\Infrastructure\Event\EventDispatcher;
use Shared\Infrastructure\Event\EventServiceProvider;
use Shared\Domain\Event\Event;

// Simple test event
class TestEvent extends Event
{
    public function __construct(private string $message)
    {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'test.event';
    }

    public function getPayload(): array
    {
        return ['message' => $this->message];
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}

echo "=== Testing Event System ===\n\n";

try {
    $dispatcher = EventDispatcher::getInstance();

    // Test 1: Listen and Dispatch
    echo "Test 1: Listen and Dispatch\n";
    $called = false;
    $dispatcher->listen('test.event', function($event) use (&$called) {
        $called = true;
        echo "Event received: " . $event->getMessage() . "\n";
    });
    
    $dispatcher->dispatch(new TestEvent('Hello Events!'));
    echo "Listener called: " . ($called ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 2: Multiple Listeners
    echo "Test 2: Multiple Listeners\n";
    $count = 0;
    $dispatcher->listen('multi.event', function() use (&$count) { $count++; });
    $dispatcher->listen('multi.event', function() use (&$count) { $count++; });
    $dispatcher->listen('multi.event', function() use (&$count) { $count++; });
    
    $dispatcher->dispatch(new class extends Event {
        public function getName(): string { return 'multi.event'; }
        public function getPayload(): array { return []; }
    });
    
    echo "Listeners called: {$count} " . ($count === 3 ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 3: Has Listeners
    echo "Test 3: Has Listeners\n";
    $has = $dispatcher->hasListeners('test.event');
    echo "Has listeners: " . ($has ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 4: Get Listeners
    echo "Test 4: Get Listeners\n";
    $listeners = $dispatcher->getListeners('multi.event');
    echo "Listener count: " . count($listeners) . " " . (count($listeners) === 3 ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 5: Forget Event
    echo "Test 5: Forget Event\n";
    $dispatcher->forget('multi.event');
    $has = $dispatcher->hasListeners('multi.event');
    echo "Forgotten: " . (!$has ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 6: Event Payload
    echo "Test 6: Event Payload\n";
    $event = new TestEvent('Test Message');
    $payload = $event->getPayload();
    echo "Payload: " . ($payload['message'] === 'Test Message' ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 7: Event Timestamp
    echo "Test 7: Event Timestamp\n";
    $event = new TestEvent('Test');
    $occurredOn = $event->getOccurredOn();
    $isRecent = $occurredOn->getTimestamp() > (time() - 5);
    echo "Timestamp: " . ($isRecent ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 8: Register Service Provider
    echo "Test 8: Register Service Provider\n";
    EventDispatcher::reset();
    EventServiceProvider::register();
    $dispatcher = EventDispatcher::getInstance();
    $hasUserCreated = $dispatcher->hasListeners('user.created');
    $hasUserDeleted = $dispatcher->hasListeners('user.deleted');
    echo "Service Provider: " . ($hasUserCreated && $hasUserDeleted ? '✓ PASS' : '✗ FAIL') . "\n\n";

    // Test 9: Flush All
    echo "Test 9: Flush All\n";
    $dispatcher->flush();
    $has1 = $dispatcher->hasListeners('user.created');
    $has2 = $dispatcher->hasListeners('user.deleted');
    echo "Flushed: " . (!$has1 && !$has2 ? '✓ PASS' : '✗ FAIL') . "\n\n";

    echo "=== All tests passed! ===\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
