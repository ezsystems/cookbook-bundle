<?php
/**
 * File containing the HideLocationCommand class.
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
 * This command demonstrates how to hide and unhide a location (subtree)
 *
 * @author christianbacher
 *
 */
class HideLocationCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:addlocation' )->setDefinition(
            array(
                new InputArgument( 'locationId', InputArgument::REQUIRED, 'An existing location id' ),
            )
        );
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        // fetch the location argument
        $locationId = $input->getArgument( 'locationId' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get the services from the repsitory
        $locationService = $repository->getLocationService();
        $userService = $repository->getUserService();

        // load the admin user and set it has current user in the repository
        $user = $userService->loadUser( 14 );
        $repository->setCurrentUser( $user );

        try
        {
            // hide the location and print out
            $location = $contentService->loadContentInfo( $contentId );
            $hiddenLocation = $locationService->hideLocation( $location );
            print_r( $hiddenLocation );

            // unhide the now hidden location and print out
            $unhiddenLocation = $locationService->unhideLocation( $hiddenLocation );
            print_r( $unhiddenLocation );

        }
        catch( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // react on location not found
            $output->writeln( "No location with id $locationId" );
        }
    }
}
