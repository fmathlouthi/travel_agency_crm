<?php
/**
 * Created by Hugues
 */

namespace App\Service;

use App\Entity\Contact;

/**
 * Sets the QRCode for new loyalty cards
 * along with useful information about the company, the customer and his card number
 */
class QRCodeEncoder
{

    /**
     * Generates QRCode for new loyalty cards
     * @param int $cardCode
     * @param string $custName
     * @return string
     */
    public function encodeQRCode(int $cardCode, string $custName) {

        $qRCode = 'Toutes les infos sur votre reword:'.PHP_EOL
            . 'TUNINFOFORYOU CRM'.PHP_EOL

            . 'FADI MATHLOUTHI:'.PHP_EOL
            . 'www.google.com'.PHP_EOL

            . 'Votre compte, scores et carte de fidélité:'.PHP_EOL
            . 'welcome'.PHP_EOL

            . 'A bientôt pour une partie !'.PHP_EOL
            . 'f@f.f'.PHP_EOL

            . 'Votre code carte ' . $cardCode . PHP_EOL
            . $custName ;

        return $qRCode;

    }

}
