<?php
// Helper functions
if (!function_exists('check_value_is_null')) {
    function check_value_is_null($val): bool
    {
        return !empty(trim($val));
    }
}
