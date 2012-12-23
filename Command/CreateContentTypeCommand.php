<?php
/**
 * File containing the CreateContentTypeCommand class.
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
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

class CreateContentTypeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:create_content_type' )->setDefinition(
            array(
                new InputArgument( 'identifier', InputArgument::REQUIRED, 'a content type identifier' ),
                new InputArgument( 'group_identifier', InputArgument::REQUIRED, 'a content type group identifier' )
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentTypeService = $repository->getContentTypeService();

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        // fetch command line arguments
        $groupIdentifier = $input->getArgument( 'group_identifier' );
        $contentTypeIdentifier = $input->getArgument( 'identifier' );

        try
        {
            $contentTypeGroup = $contentTypeService->loadContentTypeGroupByIdentifier( $groupIdentifier );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( "content type group with identifier $groupIdentifier not found" );
            return;
        }

        // instantiate a ContentTypeCreateStruct with the given content type identifier and set parameters
        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct( $contentTypeIdentifier );
        $contentTypeCreateStruct->mainLanguageCode = 'eng-GB';
        // We set the Content Type naming pattern to the title's value
        $contentTypeCreateStruct->nameSchema = '<title>';

        // set names for the content type
        $contentTypeCreateStruct->names = array(
            'eng-GB' => $contentTypeIdentifier . 'eng-GB',
            // 'ger-DE' => $contentTypeIdentifier . 'ger-DE',
        );

        // set description for the content type
        $contentTypeCreateStruct->descriptions = array(
            'eng-GB' => 'Description for ' . $contentTypeIdentifier . ' [eng-GB]',
            // 'ger-DE' => 'Description for ' . $contentTypeIdentifier . ' [ger-DE]',
        );

        // add a TextLine Field with identifier 'title'
        $titleFieldCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct( 'title', 'ezstring' );
        $titleFieldCreateStruct->names = array( 'eng-GB' => 'Title'/*, 'ger-DE' => 'Titel'*/ );
        $titleFieldCreateStruct->descriptions = array( 'eng-GB' => 'The Title'/*, 'ger-DE' => 'Der Titel'*/ );
        $titleFieldCreateStruct->fieldGroup = 'content';
        $titleFieldCreateStruct->position = 10;
        $titleFieldCreateStruct->isTranslatable = true;
        $titleFieldCreateStruct->isRequired = true;
        $titleFieldCreateStruct->isSearchable = true;
        $contentTypeCreateStruct->addFieldDefinition( $titleFieldCreateStruct );

        // add a TextLine Field body field
        $bodyFieldCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct( 'body', 'ezstring' );
        $bodyFieldCreateStruct->names = array( 'eng-GB' => 'Body'/*, 'ger-DE' => 'Text'*/ );
        $bodyFieldCreateStruct->descriptions = array( 'eng-GB' => 'Description for Body'/*, 'ger-DE' => 'Beschreibung Text'*/ );
        $bodyFieldCreateStruct->fieldGroup = 'content';
        $bodyFieldCreateStruct->position = 20;
        $bodyFieldCreateStruct->isTranslatable = true;
        $bodyFieldCreateStruct->isRequired = true;
        $bodyFieldCreateStruct->isSearchable = true;
        $contentTypeCreateStruct->addFieldDefinition( $bodyFieldCreateStruct );

        try
        {
            $contentTypeDraft = $contentTypeService->createContentType( $contentTypeCreateStruct, array( $contentTypeGroup ) );
            $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
            $output->writeln( "<info>Content type created '$contentTypeIdentifier' with ID $contentTypeDraft->id" );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            $output->writeln( "<error>" . $e->getMessage() . "</error>" );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\ForbiddenException $e )
        {
            $output->writeln( "<error>" . $e->getMessage() . "</error>" );
        }
    }
}