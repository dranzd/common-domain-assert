<?php

namespace Dranzd\Common\EventSourcing\Domain\Event;

/**
 * Interface for event upcasters
 * 
 * Upcasters transform old event versions to newer versions.
 * This is essential for event sourcing when event schemas evolve.
 * 
 * Example:
 * 
 * class AttachmentAddedUpcaster implements EventUpcaster
 * {
 *     public function canUpcast(string $eventName, int $version): bool
 *     {
 *         return $eventName === 'booking.event.attachment-added' && $version === 1;
 *     }
 *     
 *     public function upcast(array $eventData): array
 *     {
 *         // Transform V1 to V2
 *         $eventData['payload']['file_size'] = $eventData['payload']['size'] ?? 0;
 *         unset($eventData['payload']['size']);
 *         
 *         $eventData['metadata']['_aggregate_event_version'] = 2;
 *         
 *         return $eventData;
 *     }
 * }
 */
interface EventUpcaster
{
    /**
     * Check if this upcaster can handle the given event version
     *
     * @param string $eventName The event name/type
     * @param int $version The current version of the event
     * @return bool
     */
    public function canUpcast(string $eventName, int $version): bool;

    /**
     * Transform the event data to the next version
     * 
     * The event data array should contain:
     * - message_uuid: string
     * - message_name: string
     * - created_at: string
     * - metadata: array
     * - payload: array
     *
     * @param array $eventData The event data to upcast
     * @return array The upcasted event data
     */
    public function upcast(array $eventData): array;

    /**
     * Get the target version this upcaster produces
     *
     * @return int
     */
    public function toVersion(): int;
}
