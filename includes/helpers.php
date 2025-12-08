<?php
// includes/helpers.php

if (!function_exists('renderStatusPill')) {
    function renderStatusPill($statusRaw) {
        $status = strtolower(trim((string)$statusRaw));
        $class  = 'status-pill status-pill--default';
        $label  = ucfirst($status);

        switch ($status) {
            case 'pending':
                $class = 'status-pill status-pill--pending';
                $label = 'Pending';
                break;
            case 'approved':
                $class = 'status-pill status-pill--approved';
                $label = 'Approved';
                break;
            case 'rejected':
                $class = 'status-pill status-pill--rejected';
                $label = 'Rejected';
                break;
            case 'returned':
                $class = 'status-pill status-pill--returned';
                $label = 'Returned';
                break;
            default:
                if ($label === '' || $label === ' ') {
                    $label = 'N/A';
                }
        }

        return '<span class="' . $class . '">' .
               htmlspecialchars($label, ENT_QUOTES, 'UTF-8') .
               '</span>';
    }
}
