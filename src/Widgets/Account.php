<?php

/**
 * This file is part of the pdAdmin package.
 *
 * @package     pd-admin
 * @license     LICENSE
 * @author      Ramazan APAYDIN <apaydin541@gmail.com>
 * @link        https://github.com/appaydin/pd-admin
 */

namespace App\Widgets;

use App\Entity\Account\User;
use App\Entity\Contact;
use App\Entity\LoyaltyCardsRequests;
use App\Entity\Promotion;
use Doctrine\ORM\EntityManagerInterface;
use Pd\WidgetBundle\Builder\Item;
use Pd\WidgetBundle\Event\WidgetEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * Account Widget.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */
class Account
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Account Constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Build Widgets.
     */
    public function builder(WidgetEvent $event)
    {
        // Get Widget Container
        $widgets = $event->getWidgetContainer();

        // Add Widgets
        $widgets
            ->addWidget(
                (new Item('user_info'))
                    ->setGroup('admin')
                    ->setName('widget_user_info.name')
                    ->setDescription('widget_user_info.description')
                    ->setTemplate('Admin/Widget/userInfo.html.twig')
                    ->setRole(['ROLE_WIDGET_USERINFO'])
                    ->setData(function ($config) {
                        $userCount = $this->entityManager->getRepository(User::class)
                            ->createQueryBuilder('u')
                            ->select('count(u.id)')
                            ->getQuery()
                            ->getSingleScalarResult();

                        return ['result' => $userCount];
                    })
                    ->setOrder(5)
            )
            ->addWidget(
                (new Item('customersTotal'))
                    ->setGroup('admin')
                    ->setName('widget_customerstotal.name')
                    ->setDescription('widget_customerstotal.description')
                    ->setTemplate('Admin/Widget/customerstotal.html.twig')
                    ->setRole(['ROLE_WIDGET_CUSTOMERTOTAL'])
                    ->setData(function ($config) {
                        $userCount = $this->entityManager->getRepository(Contact::class)
                            ->createQueryBuilder('u')
                            ->select('count(u.id)')
                            ->getQuery()
                            ->getSingleScalarResult();

                        return ['result' => $userCount];
                    })
                    ->setOrder(5)
            )

            ->addWidget(
                (new Item('valdatecard'))
                    ->setGroup('admin')
                    ->setName('widget_valdatecard.name')
                    ->setDescription('widget_valdatecard.description')
                    ->setTemplate('Admin/Widget/valdatecard.html.twig')
                    ->setRole(['ROLE_WIDGET_VALDATECARD'])
                    ->setData(function ($config) {
                        $userCount = $this->entityManager->getRepository(LoyaltyCardsRequests::class)
                            ->createQueryBuilder('lcr')
                            ->select('count(lcr.id)')
                            ->WHERE (' lcr.status = 1  ')
                            ->getQuery()
                            ->getSingleScalarResult();

                        return ['result' => $userCount];
                    })
                    ->setOrder(5)
            )
            ->addWidget(
                (new Item('refusedcard'))
                    ->setGroup('admin')
                    ->setName('widget_refusedcard.name')
                    ->setDescription('widget_refusedcard.description')
                    ->setTemplate('Admin/Widget/refusedcard.html.twig')
                    ->setRole(['ROLE_WIDGET_REFUSEDCARD'])
                    ->setData(function ($config) {
                        $userCount = $this->entityManager->getRepository(LoyaltyCardsRequests::class)
                            ->createQueryBuilder('lcr')
                            ->select('count(lcr.id)')
                            ->WHERE (' lcr.status = 2  ')
                            ->getQuery()
                            ->getSingleScalarResult();

                        return ['result' => $userCount];
                    })
                    ->setOrder(5)
            )
            ->addWidget(
                (new Item('requestedcard'))
                    ->setGroup('admin')
                    ->setName('widget_requestedcard.name')
                    ->setDescription('widget_requestedcard.description')
                    ->setTemplate('Admin/Widget/requestedcard.html.twig')
                    ->setRole(['ROLE_WIDGET_REQUESTEDCARD'])
                    ->setData(function ($config) {
                        $userCount = $this->entityManager->getRepository(LoyaltyCardsRequests::class)
                            ->createQueryBuilder('lcr')
                            ->select('count(lcr.id)')

                            ->getQuery()
                            ->getSingleScalarResult();

                        return ['result' => $userCount];
                    })
                    ->setOrder(5)
            )
            ->addWidget(
                (new Item('inactiveadmin'))
                    ->setGroup('admin')
                    ->setName('widget_inactiveadmin.name')
                    ->setDescription('widget_inactiveadmin.description')
                    ->setTemplate('Admin/Widget/inactiveadmin.html.twig')
                    ->setRole(['ROLE_WIDGET_INACTIVEADMIN'])
                    ->setData(function ($config) {
                        $userCount = $this->entityManager->getRepository(User::class)
                            ->createQueryBuilder('u')
                            ->select('count(u.id)')
                            ->WHERE (' u.isActive = 0  ')
                            ->getQuery()
                            ->getSingleScalarResult();

                        return ['result' => $userCount];
                    })
                    ->setOrder(5)
            )
            ->addWidget(
                (new Item('activeorpmotion'))
                    ->setGroup('admin')
                    ->setName('widget_activepromotion.name')

                    ->setDescription('widget_activepromotion.description')
                    ->setTemplate('Admin/Widget/activepromotion.html.twig')
                    ->setRole(['ROLE_WIDGET_ACTIVEPROMOTION'])
                    ->setData(function ($config) {
                        $userCount = $this->entityManager->getRepository(Promotion::class)
                            ->createQueryBuilder('p')
                            ->select('count(p.id)')
                            ->WHERE ('  p.startsAt  <=  :now  and  p.endsAt  >= :now ')
                            ->setParameter('now',new \DateTime('now'))
                            ->getQuery()
                            ->getSingleScalarResult();

                        return ['result' => $userCount];
                    })
                    ->setOrder(5)
            )

            ->addWidget(
                (new Item('contact_birthday'))
                    ->setGroup('admin')
                    ->setName('widget_clinetsbirthday.name')
                    ->setDescription('widget_clinetsbirthday.description')
                    ->setTemplate('Admin/Widget/contactBirthday.html.twig')
                    ->setRole(['ROLE_WIDGET_CONTACTBIRTHDAY'])
                    ->setData(function ($config) {
                        $birthday = $this->entityManager->getRepository(Contact::class)
                            ->createQueryBuilder('u')
                            ->select('count(u.id)')
                            ->WHERE (' MONTH ( u.birhday ) = MONTH( :now ) and DAY ( u.birhday ) = DAY ( :now )  ')
                            ->setParameter('now',new \DateTime('now'))

                            ->getQuery()
                            ->getSingleScalarResult();

                        return ['birthday' => $birthday];
                    })

            )
            ->addWidget(
                (new Item('user_statistics'))
                    ->setGroup('admin')
                    ->setName('widget_user_statistics.name')
                    ->setDescription('widget_user_statistics.description')
                    ->setTemplate('Admin/Widget/userStatistics.html.twig')
                    ->setRole(['ROLE_WIDGET_USERSTATISTICS'])
                    ->setConfigProcess(static function (Request $request) {
                        if ($type = $request->get('type')) {
                            switch ($type) {
                                case '1week':
                                    return ['type' => '1week'];
                                case '1month':
                                    return ['type' => '1month'];
                                case '3month':
                                    return ['type' => '3month'];
                            }
                        }

                        return false;
                    })
                    ->setData(function ($config) {
                        // Create Chart Data
                        $chart = [
                            'column' => [],
                            'created' => [],
                            'logged' => [],
                        ];

                        // Set Default
                        if (!isset($config['type'])) {
                            $config['type'] = '1week';
                        }

                        // Create Statistics Data
                        if ('3month' === $config['type']) {
                            // Load Records
                            $createdData = $this->entityManager->getRepository(User::class)
                                ->createQueryBuilder('u')
                                ->select('count(u.id) as count, MONTH(u.createdAt) as month')
                                ->groupBy('month')
                                ->where('u.createdAt >= :date')
                                ->setParameter('date', new \DateTime('-3 Month'))
                                ->getQuery()->getArrayResult();
                            $loggedData = $this->entityManager->getRepository(User::class)
                                ->createQueryBuilder('u')
                                ->select('count(u.id) as count, MONTH(u.lastLogin) as month')
                                ->groupBy('month')
                                ->where('u.lastLogin >= :date')
                                ->setParameter('date', new \DateTime('-3 Month'))
                                ->getQuery()->getArrayResult();
                            $createdData = array_column($createdData, 'count', 'month');
                            $loggedData = array_column($loggedData, 'count', 'month');

                            // Optimize Data
                            for ($i = 0; $i < 3; ++$i) {
                                $month = explode('/', date('n/Y', strtotime("-{$i} month")));
                                $chart['column'][] = $month[0].'/'.$month[1];
                                $chart['created'][] = $createdData[$month[0]] ?? 0;
                                $chart['logged'][] = $loggedData[$month[0]] ?? 0;
                            }
                        } elseif (\in_array($config['type'], ['1month', '1week'], true) || !$config['type']) {
                            $time = '1month' === $config['type'] ? new \DateTime('-1 Month') : new \DateTime('-6 Day');
                            $column = '1month' === $config['type'] ? 30 : 7;

                            // Load Records
                            $createdData = $this->entityManager->getRepository(User::class)
                                ->createQueryBuilder('u')
                                ->select('count(u.id) as count, DAY(u.createdAt) as day')
                                ->groupBy('day')
                                ->where('u.createdAt >= :date')
                                ->setParameter('date', $time)
                                ->getQuery()->getArrayResult();
                            $loggedData = $this->entityManager->getRepository(User::class)
                                ->createQueryBuilder('u')
                                ->select('count(u.id) as count, DAY(u.lastLogin) as day')
                                ->groupBy('day')
                                ->where('u.lastLogin >= :date')
                                ->setParameter('date', $time)
                                ->getQuery()->getArrayResult();
                            $createdData = array_column($createdData, 'count', 'day');
                            $loggedData = array_column($loggedData, 'count', 'day');

                            // Optimize Data
                            for ($i = 0; $i < $column; ++$i) {
                                $day = explode('/', date('j/m', strtotime("-{$i} day")));
                                $chart['column'][] = $day[0].'/'.$day[1];
                                $chart['created'][] = $createdData[$day[0]] ?? 0;
                                $chart['logged'][] = $loggedData[$day[0]] ?? 0;
                            }
                        }

                        // JSON & Reverse Data
                        $chart['column'] = json_encode(array_reverse($chart['column']));
                        $chart['created'] = json_encode(array_reverse($chart['created']));
                        $chart['logged'] = json_encode(array_reverse($chart['logged']));

                        return $chart;
                    })
            );
    }
}
