<?php
/**
 * File containing the CopyContentCommand class.
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
    Symfony\Component\Console\Input\InputOption;

/**
 * this command copies a content object to a new location
 *
 * @author christianbacher
 */
class CopyContentCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:copycontent' )->setDefinition(
            array(
                new InputArgument( 'contentId', InputArgument::REQUIRED, 'An existing content id' ),
                new InputArgument( 'parentLocationId', InputArgument::REQUIRED, 'An existing parent location (node) id' ),
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
        // fetch input arguments
        $parentLocationId = $input->getArgument( 'parentLocationId' );
        $contentId = $input->getArgument( 'contentId' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get the services from the repsitory
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $userService = $repository->getUserService();

        // load the admin user and set it has current user in the repository
        $user = $userService->loadUser( 14 );
        $repository->setCurrentUser( $user );

        try
        {
            // instanciate a location create struct for the given parent location and load the content info
            $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocationId );
            $contentInfo = $contentService->loadContentInfo( $contentId );

            // copy the content - all versions are also copied. If only a specific version
            // should be copied it can be passed as third parameter
            // NOTE: the children are not copied wit this method - use LocationService::copySubtree instead
            $copiedContent = $contentService->copyContent( $contentInfo, $locationCreateStruct );

            // print out the content
            print_r( $copiedContent );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // react on content not found
            $output->writeln( $e->getMessage() );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            // react on permission denied
            $output->writeln( $e->getMessage() );
        }
    }
}

