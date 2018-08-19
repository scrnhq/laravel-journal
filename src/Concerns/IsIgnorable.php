<?php

namespace Scrn\Journal\Concerns;

trait IsIgnorable
{
    /**
     * Checks whether current event needs to be ignored or not.
     *
     * @param string $event
     *
     * @return bool
     */
    protected function ignore(string $event): bool
    {
        $updated_attributes = array_keys($this->getDirty());

        foreach ($this->ignore_activities ?? [] as $event_rule => $ignored_attributes) {
            if ($event_rule !== $event) {
                continue;
            }

            $similarities = array_intersect($ignored_attributes, $updated_attributes);

            // If all ignored attributes are not similar to all updated attributes.
            if (count($similarities) !== count($updated_attributes)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
