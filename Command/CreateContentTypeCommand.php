<?php
/**
 * File containing the CreateContentTypeCommand class.
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
    Symfony\Component\Console\Input\InputOption,
    eZ\Publish\API\Repository\Repository,
    eZ\Publish\API\Repository\ContentTypeService,
    eZ\Publish\API\Repository\ContentService,
    eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * this command creates a simple content type with the two fields 'title' and 'body'
 *
 * @author christianbacher
 *
 */
class CreateContentTypeCommand extends ContainerAwareCommand
{

    /**
     * Add an input argument for the identifier for the group where the content type should be created
     * and an identifier for the content type
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:createcontenttype' )->setDefinition(
            array(
                new InputArgument( 'content_type_group_identifier', InputArgument::REQUIRED, 'a content type group identifier' ),
                new InputArgument( 'content_type_identifier', InputArgument::REQUIRED, 'a content type identifier' )
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        // fetch command line arguments
        $groupIdentifier = $input->getArgument( 'content_type_group_identifier' );
        $contentTypeIdentifier = $input->getArgument( 'content_type_identifier' );

        // get the repository from the di container
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );

        // get the services from the repsitory
        $contentTypeService = $repository->getContentTypeService();
        $userService = $repository->getUserService();

        // load the admin user and set it has current user in the repository
        $user = $userService->loadUser( 14 );
        $repository->setCurrentUser( $user );

        // load the content type group
        try
        {
            $contentTypeGroup = $contentTypeService->loadContentTypeGroupByIdentifier( $groupIdentifier );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( "content type group with identifier $groupIdentifier not found" );
            return;
        }

        // instanciate a ContentTypeCreateStruct with the given content type identifier and set parameters
        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct( $contentTypeIdentifier );
        $contentTypeCreateStruct->mainLanguageCode = 'eng-GB'; // the main language code for names and description
        $contentTypeCreateStruct->nameSchema = '<title>'; // the name schema for generating the content name by using the title attribute

        // set names for the content type
        $contentTypeCreateStruct->names = array(
            'eng-GB' => $contentTypeIdentifier . 'eng-GB',
            'ger-DE' => $contentTypeIdentifier . 'ger-DE',
        );

        // set description for the content type
        $contentTypeCreateStruct->descriptions = array(
            'eng-GB' => 'Description for ' . $contentTypeIdentifier . 'eng-GB',
            'ger-DE' => 'Description for ' . $contentTypeIdentifier . 'ger-DE',
        );

        /********************** add fields ***************************************/

        // add a title field
        $titleFieldCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct( 'title', 'ezstring' );
        $titleFieldCreateStruct->names = array( 'eng-GB' => 'Title', 'ger-DE' => 'Titel' ); // set names
        $titleFieldCreateStruct->descriptions = array( 'eng-GB' => 'The Title', 'ger-DE' => 'Der Titel' ); // set descriptions
        $titleFieldCreateStruct->fieldGroup = 'content'; // set an group for the field definition
        $titleFieldCreateStruct->position = 10; // set position inside the content type
        $titleFieldCreateStruct->isTranslatable = true; // enable translation
        $titleFieldCreateStruct->isRequired = true; // require this field to set on content creation
        $titleFieldCreateStruct->isSearchable = true; // enabled to find field via content search

        // add field definition to content create struct
        $contentTypeCreateStruct->addFieldDefinition( $titleFieldCreateStruct );

        // add a body field
        $bodyFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( 'body', 'ezstring' );
        $bodyFieldCreate->names = array( 'eng-GB' => 'Body', 'ger-DE' => 'Text' );
        $bodyFieldCreate->descriptions = array( 'eng-GB' => 'Description for Body', 'ger-DE' => 'Beschreibung Text' );
        $bodyFieldCreate->fieldGroup = 'content';
        $bodyFieldCreate->position = 20;
        $bodyFieldCreate->isTranslatable = true;
        $bodyFieldCreate->isRequired = true;
        $bodyFieldCreate->isSearchable = true;

        // add field definition to content create struct
        $contentTypeCreateStruct->addFieldDefinition( $bodyFieldCreate );

        // set the content type group for the content type
        $groups = array( $contentTypeGroup );

        try
        {
            // create the content type draft and publish it
            $contentTypeDraft = $contentTypeService->createContentType( $contentTypeCreateStruct,$groups );
            $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            // react on permission denied
            $output->writeln( $e->getMessage() );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\ForbiddenException $e )
        {
            // react on identifier already exists
            $output->writeln( $e->getMessage() );
        }
        catch( \Exception $e )
        {
            $output->writeln( $e->getMessage() );
        }
    }
}

