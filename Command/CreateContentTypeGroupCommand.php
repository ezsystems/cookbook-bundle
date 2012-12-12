<?php
/**
 * File containing the CreateContentTypeGroupCommand class.
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
 * With this command a content type group can be created
 *
 * @author christianbacher
 */
class CreateContentTypeGroupCommand extends ContainerAwareCommand
{

    /**
     * Add an input argument for the identifier for the new group
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:createcontenttypegroup' )->setDefinition(
            array(
                new InputArgument( 'content_type_group_identifier', InputArgument::REQUIRED, 'a content type group identifier' ),
            )
        );
    }

    /**
     * Executes create group command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {

        // fetch command line arguments
        $contentTypeGroupIdentifier = $input->getArgument( 'content_type_group_identifier' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get the services from the repsitory
        $contentTypeService = $repository->getContentTypeService();
        $userService = $repository->getUserService();

        // load the admin user and set it has current user in the repository
        $user = $userService->loadUser( 14 );
        $repository->setCurrentUser( $user );

        try
        {
            // instanciate a create struct and create the group
            $contentTypeGroupCreateStruct = $contentTypeService->newContentTypeGroupCreateStruct( $contentTypeGroupIdentifier );
            $contentTypeGroup =  $contentTypeService->createContentTypeGroup( $contentTypeGroupCreateStruct );

            // print out the group
            print_r( $contentTypeGroup );
        }
        catch ( UnauthorizedException $e )
        {
            // react on permission denied
            $output->writeln( $e->getMessage() );
        }
        catch ( ForbiddenException $e )
        {
            // react on identifier already exists
            $output->writeln( $e->getMessage() );
        }
    }
}

