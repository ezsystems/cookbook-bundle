<?php
/**
 * File containing the ViewContentCommand class.
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
 * With this command content can be viewd.
 * For a given content id there is an iteration over the field definitions
 * and the field values are shown.
 *
 * @author christianbacher
 */
class ViewContentCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:viewcontent' )->setDefinition(
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
        // fetch the input argument
        $contentId = $input->getArgument( 'contentId' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get the services from the repsitory
        $contentService = $repository->getContentService();
        $fieldTypeService = $repository->getFieldTypeService();

        try
        {
            // iterate over the field definitions of the content type and print out the identifier and value
            $content = $contentService->loadContent( $contentId );
            $contentType = $content->contentType;
            foreach( $contentType->fieldDefinitions as $fieldDefinition )
            {
                if( $fieldDefinition->fieldTypeIdentifier == 'ezpage' ) continue; // ignore ezpage
                $output->write( $fieldDefinition->identifier . ": " );

                $fieldType = $fieldTypeService->getFieldType( $fieldDefinition->fieldTypeIdentifier );
                $field = $content->getField( $fieldDefinition->identifier );
                $valueHash = $fieldType->toHash( $field->value ); // use toHash for getting a readable output
                $output->writeln( $valueHash );
            }
        }
        catch( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // if the id is not found
            $output->writeln( "No content with id $contentId" );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            // not allowed to read this content
            $output->writeln( "Anonymous users are not allowed to read content with id $contentId" );
        }
    }
}
