<?php

return [
    /*
     * Gültigkeitsdauer eines 2FA-Codes in Minuten. Steuert sowohl die
     * tfa_expires_at-Berechnung (TwofaService::generate()) als auch den
     * angezeigten Text in E-Mail und Login-Hinweistexten (cust/mand/syst).
     */
    'valid_minutes' => (int) env('TWOFA_CODE_VALID_MINUTES', 2),
];
