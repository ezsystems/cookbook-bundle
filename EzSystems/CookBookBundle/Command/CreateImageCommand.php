<?php
/**
 * File containing the CreateImageCommand class.
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


class CreateImageCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:createimage' )->setDefinition(
            array(
                new InputArgument( 'parentLocationId', InputArgument::REQUIRED, 'An existing parent location (node) id' ),
                new InputArgument( 'name' , InputArgument::REQUIRED, 'the name of the image'),
                new InputArgument( 'file' , InputArgument::REQUIRED, 'the absolute path of the image file')
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
        $name = $input->getArgument( 'name' );
        $file = $input->getArgument( 'file' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get the services from the repsitory
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $userService = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        // load the admin user and set it has current user in the repository
        $user = $userService->loadUser( 14 );
        $repository->setCurrentUser( $user );

        try
        {
            // load the image content type and instanciate a content create struct
            $contentType = $contentTypeService->loadContentTypeByIdentifier( "image" );
            $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
            $contentCreateStruct->setField('name', $name ); // set name field

            // set image file field
            $value = new \eZ\Publish\Core\FieldType\Image\Value(
                array(
                    'path' => $file,
                    'fileSize' => filesize($file),
                    'fileName' => basename($file),
                    'alternativeText' => $name
                )
            );
            $contentCreateStruct->setField( 'image' , $value );

            // instanciate a location create struct and create and publish the content
            $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocationId );
            $draft = $contentService->createContent( $contentCreateStruct,array( $locationCreateStruct ) );
            $content = $contentService->publishVersion( $draft->versionInfo );
            print_r( $content );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // react on content type or location not found
            $output->writeln( $e->getMessage() );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException $e )
        {
            // react on remote id exists already
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
