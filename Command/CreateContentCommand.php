<?php
/**
 * File containing the CreateContentCommand class.
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
 * this command creates a simple content object containing a title and a body.
 *
 * @author christianbacher
 *
 */
class CreateContentCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:createcontent' )->setDefinition(
            array(
                new InputArgument( 'parentLocationId', InputArgument::REQUIRED, 'An existing parent location (node) id' ),
                new InputArgument( 'contentType', InputArgument::REQUIRED, 'An existing content type identifier - the content type must contain a title field and a body field' ),
                new InputArgument( 'title' , InputArgument::REQUIRED, 'the title of the content' ),
                new InputArgument( 'body' , InputArgument::REQUIRED, 'the body of the content' )
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
        // fetch the input arguments
        $parentLocationId = $input->getArgument( 'parentLocationId' );
        $contentTypeIdentifier = $input->getArgument( 'contentType' );
        $title = $input->getArgument( 'title' );
        $body = $input->getArgument( 'body' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get the services from the repsitory
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $contentTypeService = $repository->getContentTypeService();
        $userService = $repository->getUserService();

        // load the admin user and set it has current user in the repository
        $user = $userService->loadUser( 14 );
        $repository->setCurrentUser( $user );

        try
        {
            // load the content type and instanciate a content create struct and set title and body fields
            $contentType = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
            $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
            $contentCreateStruct->setField( 'title', $title );
            $contentCreateStruct->setField( 'body', $body );

            // instanciate a location create struct from the parent location
            $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocationId );

            // create a draft using the content and location create structs and publish it
            $draft = $contentService->createContent( $contentCreateStruct, array( $locationCreateStruct ) );
            $content = $contentService->publishVersion( $draft->versionInfo );

            // print out the content
            print_r( $content );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // react on content type or location not found
            $output->writeln( $e->getMessage() );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException $e )
        {
            // react on a field is not valid
            $output->writeln( $e->getMessage() );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\ContentValidationException $e )
        {
            // react on a required field is missing or empty
            $output->writeln( $e->getMessage() );
        }
    }
}
