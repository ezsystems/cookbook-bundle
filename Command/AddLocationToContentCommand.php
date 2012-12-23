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
 */
class AddLocationToContentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:addlocation' )->setDefinition(
            array(
                new InputArgument( 'contentId', InputArgument::REQUIRED, 'An existing content id' ),
                new InputArgument( 'parentLocationId', InputArgument::REQUIRED, 'An existing parent location (node) id' ),
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $repository->setCurrentUser( $userService = $repository->getUserService()->loadUser( 14 ) );

        // fetch the input arguments
        $parentLocationId = $input->getArgument( 'parentLocationId' );
        $contentId = $input->getArgument( 'contentId' );

        try
        {
            $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocationId );
            $contentInfo = $contentService->loadContentInfo( $contentId );
            $newLocation = $locationService->createLocation( $contentInfo, $locationCreateStruct );

            print_r( $newLocation );
        }
        // Content or location not found
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( $e->getMessage() );
        }
        // Permission denied
        catch ( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            $output->writeln( $e->getMessage() );
        }

    }
}


