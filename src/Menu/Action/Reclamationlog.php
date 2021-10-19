<?php

/**
 * This file is part of the pdAdmin package.
 *
 * @package     pd-admin
 * @license     LICENSE
 * @author      Ramazan APAYDIN <apaydin541@gmail.com>
 * @link        https://github.com/appaydin/pd-admin
 */

namespace App\Menu\Action;

use Pd\MenuBundle\Builder\ItemInterface;
use Pd\MenuBundle\Builder\Menu;

/**
 * Contact Actions.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */
class Reclamationlog extends Menu
{
    public function createMenu(array $options = []): ItemInterface
    {


        // Create Root Menu
        $menu = $this->createRoot('reclamationlog_action_menu');

        // Add Menu Items
        $menu
            ->addChild('admin_reclamationlog_show', 1)
            ->setLabel('reclamation log')
            ->setRoute('admin_reclamationlog_show', ['id' => $options['reclamation']["s_id"]])
            ->setRoles(['ROLE_RECLAMATIONLOG_SHOW'])
            ->setExtra('label_icon', 'visibility')
            ->setLinkAttr([

                'data-tooltip' => '',
                'title' => 'show',

            ])
            ->setLabelAttr(['class' => 'hidden'])

;


        return $menu;
    }
}
