<?php
/**
 * File containing the ViewContentCommand class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:viewcontent' )->setDefinition(
            array(
                new InputArgument( 'contentId', InputArgument::REQUIRED, 'An existing content id' )
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
        $contentId = $input->getArgument( 'contentId' );

        // Initialize the repository and the required services

        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentService = $repository->getContentService();
        $fieldTypeService = $repository->getFieldTypeService();

        try
        {
            $content = $contentService->loadContent( $contentId );

            // Iterate over the field definitions of the content type, and print identifier: value
            foreach ( $content->contentType->fieldDefinitions as $fieldDefinition )
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
