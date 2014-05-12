<?php
/**
 * File containing the BrowseLocationsCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace EzSystems\CookbookBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption;

/**
 * This commands lists sections from the system, with ID, name and identifier
 */
class ListSectionsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:list_sections' );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $sectionService = $repository->getSectionService();
        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        try
        {
            /** @var $section \eZ\Publish\API\Repository\Values\Content\Section */
            foreach ( $sectionService->loadSections() as $section )
            {
                $output->writeln( "Section #{$section->id}: {$section->name} [$section->identifier]" );
            }
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            // react on permission denied
            $output->writeln( "Anonymous users are not allowed to list sections" );
        }
    }
}


