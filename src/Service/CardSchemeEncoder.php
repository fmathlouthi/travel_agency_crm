<?php
/**
 * Created by Hugues
 * 23/12/2018
 */

namespace App\Service;


use App\Entity\Contact;
use App\Entity\LoyaltyCard;

/**
 * Used to encode and decode loyalty card's scheme
 * Aka, the pattern that ties a center code, a customer code, and a checksum
 */
class CardSchemeEncoder
{
    /**
     * Encodes new loyalty cards

     * @param int $customerCode
     * @return int
     */
    public function encode( int $customerCode) {

        // encode the card
        $cardCode = '0015588444'. $customerCode . ((102548874+$customerCode)%9);
        $cardCode = (int)$cardCode;

        return $cardCode;
    }

    /**
     * Should we ever need it
     * @param LoyaltyCard $card
     * @return array
     */
    public function decode(LoyaltyCard $card) {

        // if necessary, get the center code and the customer code from the card
        $cardCode = (string) $card->getCardCode();

        $customerCode = substr($cardCode, 3, 6);

        $results = array(
            'centerCode' => '114552587',
            'customerCode' => $customerCode
        );
        return $results;

    }

}
