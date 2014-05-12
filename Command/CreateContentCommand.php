<?php
/**
 * File containing the CreateContentCommand class.
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


/**
 * this command creates a simple content object containing a title and a body.
 */
class CreateContentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:create_content' )->setDefinition(
            array(
                new InputArgument( 'parentLocationId', InputArgument::REQUIRED, 'An existing parent location ID' ),
                new InputArgument( 'contentType', InputArgument::REQUIRED, 'An existing content type identifier - the content type must contain a title field and a body field' ),
                new InputArgument( 'title', InputArgument::REQUIRED, 'Content for the Title field' ),
                new InputArgument( 'intro', InputArgument::REQUIRED, 'Content for the Intro field' ),
                new InputArgument( 'body', InputArgument::REQUIRED, 'Content for the Body field' ),
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
        $contentTypeIdentifier = $input->getArgument( 'contentType' );
        $title = $input->getArgument( 'title' );
        $intro = $input->getArgument( 'intro' );
        $body = $input->getArgument( 'body' );

        try
        {
            $contentType = $contentTypeService->loadContentTypeByIdentifier( $contentTypeIdentifier );
            $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
            $contentCreateStruct->setField( 'title', $title );
            $contentCreateStruct->setField( 'intro', $intro );
            $contentCreateStruct->setField( 'body', $body );

            // instantiate a location create struct from the parent location
            $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocationId );

            // create a draft using the content and location create struct and publish it
            $draft = $contentService->createContent( $contentCreateStruct, array( $locationCreateStruct ) );
            $content = $contentService->publishVersion( $draft->versionInfo );

            // print out the content
            print_r( $content );
        }
        // Content type or location not found
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( $e->getMessage() );
        }
        // Invalid field value
        catch ( \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException $e )
        {
            $output->writeln( $e->getMessage() );
        }
        // Required field missing or empty
        catch ( \eZ\Publish\API\Repository\Exceptions\ContentValidationException $e )
        {
            $output->writeln( $e->getMessage() );
        }
    }
}
