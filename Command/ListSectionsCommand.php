<?php
/**
 * File containing the BrowseLocationsCommand class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace EzSystems\CookbookBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    eZ\Publish\API\Repository\Values\Content\Section;

/**
 * This commands walks through a subtree and prints out the content names.
 *
 * @author christianbacher
 */
class ListSectionsCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:list_sections' );
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $sectionService = $repository->getSectionService();
        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        try
        {
            /** @var $section Section */
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


