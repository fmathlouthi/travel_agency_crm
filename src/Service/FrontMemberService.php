<?php
/**
 * Created by Hugues
 * 08/01/2019
 */

namespace App\Service;
use App\Entity\Account\Group;
use App\Entity\Account\User;
use App\Entity\Account\Profile;
use App\Entity\Contact;
use App\Entity\LoyaltyCardRequest;
use App\Entity\LoyaltyCardsRequests;
use App\Repository\CenterRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Factory\QrCodeFactory;
use App\Repository\LoyaltyCardsRequestsRepository;
use App\Entity\LoyaltyCards;
use App\Form\LoyaltyCardsType;
use App\Repository\LoyaltyCardsRepository;

/**
 */
class FrontMemberService
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param User $customer
     * @param QrCodeFactory $qrCodeFactory

     *
     * @return array
     */
    public function getInfoMember(array $customer, QrCodeFactory $qrCodeFactory) {





        $loyaltyCards = $this->entityManager->getRepository(LoyaltyCards::class)
            ->createQueryBuilder('lc')

            ->WHERE ('lc.customer_id = :vv')
            ->setParameter('vv',$customer[0]['c_id'] )
            ->getQuery()
            ->getScalarResult();

        if(count($loyaltyCards) > 0){
            $loyaltyCard = $loyaltyCards[0];
            $qrCodeText = $loyaltyCard["lc_qrcode"];
            // Below, the options passed to the QrCodeFactory do not work, they are overridden in config -> packages -> qrcode.yaml
            $qrCode = $qrCodeFactory->create($qrCodeText, ['size' => 60, 'label' => false, 'logo_width' => 0, 'logo_height' => 0, 'error_correction_level' => 'medium']);
        }else{
            $qrCode = "";
        }



        $cardRequest = $this->entityManager->getRepository(LoyaltyCardsRequests::class)
            ->createQueryBuilder('lcr')

            ->WHERE ('lcr.customer_id = :bb and lcr.status = 0')
            ->setParameter('bb',$customer[0]['c_id'] )
            ->getQuery()
            ->getScalarResult();

if(!empty($cardRequest))
{
    $cardRequest=$cardRequest[0];
}
        if(!empty($loyaltyCards))
        {
            $loyaltyCards=$loyaltyCards[0];
        }
        $generalInfo = [

            'card_request' => $cardRequest,
            'loyalty_cards' => $loyaltyCards,
            'qr_code' => $qrCode
        ];
        return $generalInfo;

    }
	
}
