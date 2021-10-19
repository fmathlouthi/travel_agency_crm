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
class Service extends Menu
{
    public function createMenu(array $options = []): ItemInterface
    {


        // Create Root Menu
        $menu = $this->createRoot('service_action_menu');

        // Add Menu Items
        $menu
            ->addChild('admin_service_delete', 1)
            ->setLabel('delete')
            ->setRoute('admin_service_delete', ['id' => $options['service']->getId()])
            ->setRoles(['ROLE_SERVICE_DELETE'])
            ->setExtra('label_icon', 'delete')
            ->setLinkAttr([
                'class' => 'text-danger',
                'data-tooltip' => '',
                'title' => 'delete',
                'data-modal' => 'confirm',
            ])
            ->setLabelAttr(['class' => 'hidden'])


            ->addChildParent('admin_service_edit', 1)
            ->setLabel('edit')
            ->setRoute('admin_service_edit', ['id' => $options['service']->getId()])
            ->setRoles(['ROLE_SERVICE_EDIT'])
            ->setExtra('label_icon', 'mode_edit')
            ->setLinkAttr([
                'data-tooltip' => '',
                'title' => 'Modfier',
            ])
            ->setLabelAttr(['class' => 'hidden'])

;


        return $menu;
    }
}
