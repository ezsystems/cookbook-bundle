<?php
/**
 * File containing the ViewContentCommand class.
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
 * This command loads a Content using its numerical identifier, iterates over the Content's Fields, and shows the value
 * of each of them.
 */
class ViewContentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:view_content' )->setDefinition(
            array(
                new InputArgument( 'contentId', InputArgument::REQUIRED, 'An existing content id' )
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $fieldTypeService = $repository->getFieldTypeService();

        $contentId = $input->getArgument( 'contentId' );

        try
        {
            $content = $contentService->loadContent( $contentId );
            $contentType = $contentTypeService->loadContentType( $content->contentInfo->contentTypeId );

            // Iterate over the field definitions of the content type, and print identifier: value
            foreach ( $contentType->fieldDefinitions as $fieldDefinition )
            {
                $output->writeln( "<info>" . $fieldDefinition->identifier . "</info>" );
                $fieldType = $fieldTypeService->getFieldType( $fieldDefinition->fieldTypeIdentifier );
                $field = $content->getField( $fieldDefinition->identifier );
                $valueHash = $fieldType->toHash( $field->value );
                $output->writeln( $valueHash );
            }
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( "<error>No content with id $contentId found</error>" );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            $output->writeln( "<error>Permission denied on content with id $contentId</error>" );
        }
    }
}
