<?php
namespace app\components\common;

class CommonUtils {
    public static function cleanText($text) {
        // Remove any HTML tags from the string
        $clean_text = strip_tags($text);
        // Remove any leading and trailing whitespace from the string
        $clean_text = trim($clean_text);
        // Convert HTML entities to their corresponding characters
        $clean_text = html_entity_decode($clean_text);
        // Replace multiple consecutive whitespace characters with a single space
        $clean_text = preg_replace('/\s\s+/', ' ', $clean_text);
        return $clean_text;
    }

    public static function timeSinceUpdate($lastUpdated) {
        // Check if $lastUpdated is empty or null
        if (empty($lastUpdated)) {
            return "Last updated: not available";
        }

        // Try to create a DateTime object from the $lastUpdated string
        try {
            $updatedTime = new \DateTime($lastUpdated);
        } catch (\Exception $e) {
            // If the date is invalid or throws an exception, return a default message
            return "Last updated: invalid date";
        }

        // Create a DateTime object for the last updated time
        $updatedTime = new \DateTime($lastUpdated);
        // Get the current time
        $currentTime = new \DateTime();
        
        // Calculate the difference between the two times
        $interval = $currentTime->diff($updatedTime);
        
        // Generate a user-friendly message based on the difference
        if ($interval->y > 0) {
            return "Last updated: " . $interval->y . " year" . ($interval->y > 1 ? "s" : "") . " ago";
        } elseif ($interval->m > 0) {
            return "Last updated: " . $interval->m . " month" . ($interval->m > 1 ? "s" : "") . " ago";
        } elseif ($interval->d > 0) {
            return "Last updated: " . $interval->d . " day" . ($interval->d > 1 ? "s" : "") . " ago";
        } elseif ($interval->h > 0) {
            return "Last updated: " . $interval->h . " hour" . ($interval->h > 1 ? "s" : "") . " ago";
        } elseif ($interval->i > 0) {
            return "Last updated: " . $interval->i . " minute" . ($interval->i > 1 ? "s" : "") . " ago";
        } else {
            return "Last updated: just now";
        }
    }

}