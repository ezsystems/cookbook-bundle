<?php
/**
 * File containing the AddLocationToContentCommand class.
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
 * This command adds a new location to a content object given by a content id. The location is created
 * below the given parent location.
 *
 * @author christianbacher
 */
class AddLocationToContentCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:addlocation' )->setDefinition(
            array(
                new InputArgument( 'contentId', InputArgument::REQUIRED, 'An existing content id' ),
                new InputArgument( 'parentLocationId', InputArgument::REQUIRED, 'An existing parent location (node) id' ),
            )
        );
    }

    /**
     * execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        // fetch the input arguments
        $parentLocationId = $input->getArgument( 'parentLocationId' );
        $contentId = $input->getArgument( 'contentId' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get needed the services from the repsitory
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $userService = $repository->getUserService();

        // load the admin user and set it has current user in the repository
        $user = $userService->loadUser( 14 );
        $repository->setCurrentUser( $user );


        try
        {
            // add a location to the content by instaciating a location create struct
            // from the parent location id and pass it
            // to the createLocation method along with the content info
            $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocationId );
            $contentInfo = $contentService->loadContentInfo( $contentId );
            $newLocation = $locationService->createLocation( $contentInfo, $locationCreateStruct );

            print_r($newLocation); // prints out the new location
        }
        catch( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // react on content or location not found
            $output->writeln($e->getMessage());
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            // react on permission denied
            $output->writeln($e->getMessage());
        }

    }
}


