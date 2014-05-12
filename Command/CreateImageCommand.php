<?php
/**
 * File containing the CreateImageCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace EzSystems\CookbookBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


class CreateImageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:create_image' )->setDefinition(
            array(
                new InputArgument( 'parentLocationId', InputArgument::REQUIRED, 'An existing parent location (node) id' ),
                new InputArgument( 'name', InputArgument::REQUIRED, 'the name of the image' ),
                new InputArgument( 'file', InputArgument::REQUIRED, 'the absolute path of the image file' )
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $contentTypeService = $repository->getContentTypeService();

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        // fetch the input arguments
        $parentLocationId = $input->getArgument( 'parentLocationId' );
        $name = $input->getArgument( 'name' );
        $file = $input->getArgument( 'file' );

        try
        {
            $contentType = $contentTypeService->loadContentTypeByIdentifier( "image" );
            $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
            $contentCreateStruct->setField( 'name', $name ); // set name field

            // set image file field
            $value = new \eZ\Publish\Core\FieldType\Image\Value(
                array(
                    'path' => $file,
                    'fileSize' => filesize( $file ),
                    'fileName' => basename( $file ),
                    'alternativeText' => $name
                )
            );
            $contentCreateStruct->setField( 'image', $value );

            // Create and publish the image as a child of the provided parent location
            $draft = $contentService->createContent(
                $contentCreateStruct,
                array(
                    $locationService->newLocationCreateStruct( $parentLocationId )
                )
            );
            $content = $contentService->publishVersion( $draft->versionInfo );
            print_r( $content );
        }
        // Content type or parent location not found
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( $e->getMessage() );
        }
        // Remote ID already exists
        catch ( \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException $e )
        {
            $output->writeln( $e->getMessage() );
        }
        // Invalid field
        catch ( \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException $e )
        {
            $output->writeln( $e->getMessage() );
        }
        // Missing required field, or invalid value
        catch( \eZ\Publish\API\Repository\Exceptions\ContentValidationException $e )
        {
            $output->writeln( $e->getMessage() );
        }
    }
}
