<?php
/**
 * File containing the CreateXmlContentCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\CookbookBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption;

class CreateXmlContentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:create_xmltext' )->setDefinition(
            array(
                new InputArgument( 'parentLocationId', InputArgument::REQUIRED, 'An existing parent location (node) id' ),
                new InputArgument( 'name', InputArgument::REQUIRED, 'the name of the folder' ),
                new InputArgument( 'imageId', InputArgument::REQUIRED, 'an id of an image content object' )
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
        $imageId = $input->getArgument( 'imageId' );

        try
        {
            // load a folder content type and instantiate a content creation struct
            $contentType = $contentTypeService->loadContentTypeByIdentifier( "folder" );
            $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, "eng-GB" );

            $contentCreateStruct->setField( "name", $name );
$xmlText = <<< EOX
<?xml version='1.0' encoding='utf-8'?>
<section>
<paragraph>This is a <strong>image test</strong></paragraph>
<paragraph><embed view='embed' size='medium' object_id='$imageId'/></paragraph>
</section>
EOX;
$contentCreateStruct->setField( "description", $xmlText );

            // instantiate a location create struct and create and publsidh the content
            $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocationId );
            $draft = $contentService->createContent( $contentCreateStruct, array( $locationCreateStruct ) );
            $content = $contentService->publishVersion( $draft->versionInfo );
            print_r( $content );
        }
        // ContentType or Location not found
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( $e->getMessage() );
        }
        // Remote ID exists already
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
        catch ( \eZ\Publish\API\Repository\Exceptions\ContentValidationException $e )
        {
            $output->writeln( $e->getMessage() );
        }
    }
}
