<?php

namespace App\Services;

class EmailVerificationService
{
    /**
     * Internal email verification (no external API).
     * Checks format + MX records.
     */
    public function verify(string $email): bool
    {
        // ✅ Step 1: Format check
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // ✅ Step 2: Extract domain
        $domain = substr(strrchr($email, "@"), 1);

        // ✅ Step 3: DNS MX record check
        if (checkdnsrr($domain, "MX")) {
            return true; // Domain can receive emails
        }

        return false; // Invalid domain
    }
}
