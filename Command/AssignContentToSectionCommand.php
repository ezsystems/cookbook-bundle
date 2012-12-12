<?php
/**
 * File containing the AssignContentToSectionCommand class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace EzSystems\CookBookBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption;

/**
 * This command assigns a content to a section.
 *
 * @author christianbacher
 *
 */
class AssignContentToSectionCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:assignsection' )->setDefinition(
            array(
                new InputArgument( 'contentId', InputArgument::REQUIRED, 'An existing content id' ),
                new InputArgument( 'sectionId', InputArgument::REQUIRED, 'An existing section id' ),
            )
        );
    }

    /**
     * execute the command
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        // fetch the the input arguments
        $sectionId = $input->getArgument( 'sectionId' );
        $contentId = $input->getArgument( 'contentId' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get the services from the repsitory
        $contentService = $repository->getContentService();
        $sectionService = $repository->getSectionService();
        $userService = $repository->getUserService();

        // load the admin user and set it has current user in the repository
        $user = $userService->loadUser( 14 );
        $repository->setCurrentUser( $user );

        try
        {
            // load the value objects and assign the content to the section
            $contentInfo = $contentService->loadContentInfo( $contentId );
            $section = $sectionService->loadSection( $sectionId );
            $sectionService->assignSection( $contentInfo, $section );

            // reaload the content info and print out
            $contentInfo =  $contentService->loadContentInfo( $contentId );
            $output->writeln( $contentInfo->sectionId );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // react on content or section not found
            $output->writeln( $e->getMessage() );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            // react on permission denied
            $output->writeln( $e->getMessage() );
        }

    }
}


