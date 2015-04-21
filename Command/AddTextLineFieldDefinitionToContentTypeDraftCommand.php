<?php
/**
 * File containing the CreateContentTypeCommand class.
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
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

class AddTextLineFieldDefinitionToContentTypeDraftCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:add_textline_field_definition' )->setDefinition(
            array(
                new InputArgument( 'id', InputArgument::REQUIRED, 'Content type draft id' ),
                new InputOption( 'identifier', null, InputOption::VALUE_REQUIRED, 'Field definition identifier' ),
                new InputOption( 'name', null, InputOption::VALUE_OPTIONAL, 'Field definition name (in eng-GB)' ),
                new InputOption( 'description', null, InputOption::VALUE_OPTIONAL, 'Field definition description (in eng-GB)' ),
                new InputOption( 'default-value', null, InputOption::VALUE_OPTIONAL ),
                new InputOption( 'is-required', null, InputOption::VALUE_NONE ),
                new InputOption( 'is-searchable', null, InputOption::VALUE_NONE ),
                new InputOption( 'is-translatable', null, InputOption::VALUE_NONE ),
                new InputOption( 'min-length', null, InputOption::VALUE_OPTIONAL, 'Minimum string length' ),
                new InputOption( 'max-length', null, InputOption::VALUE_OPTIONAL, 'Maximent string length' ),
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentTypeService = $repository->getContentTypeService();

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        $contentTypeId = $input->getArgument( 'id' );

        try
        {
            $contentTypeDraft = $contentTypeService->loadContentTypeDraft( $contentTypeId );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( "<error>Content type draft with identifier $contentTypeId not found</error>" );
            return;
        }

        // instantiate a ContentTypeCreateStruct with the given content type identifier and set parameters
        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct(
            $input->getOption( 'identifier' ), 'ezstring'
        );

        if ( $input->getOption( 'name' ) )
        {
            $fieldDefinitionCreateStruct->names = array( 'eng-GB' => $input->getOption( 'name' ) );
        }

        if ( $input->getOption( 'description' ) )
        {
            $fieldDefinitionCreateStruct->descriptions = array( 'eng-GB' => $input->getOption( 'description' ) );
        }

        if ( $input->getOption( 'default-value' ) )
        {
            $fieldDefinitionCreateStruct->defaultValue = array( 'eng-GB' => $input->getOption( 'default-value' ) );
        }

        $fieldDefinitionCreateStruct->isRequired = $input->getOption( 'is-required' );
        $fieldDefinitionCreateStruct->isSearchable = $input->getOption( 'is-searchable' );
        $fieldDefinitionCreateStruct->isTranslatable = $input->getOption( 'is-translatable' );

        // set description for the content type
        if ( $input->hasOption( 'min-length' ) )
        {
            $fieldDefinitionCreateStruct->validatorConfiguration['StringLengthValidator']['minStringLength'] =
                (int)$input->getOption( 'min-length' );
        }

        if ( $input->hasOption( 'max-length' ) )
        {
            $fieldDefinitionCreateStruct->validatorConfiguration['StringLengthValidator']['maxStringLength'] =
                (int)$input->getOption( 'max-length' );
        }

        try
        {
            $contentTypeService->addFieldDefinition(
                $contentTypeDraft, $fieldDefinitionCreateStruct
            );
            $output->writeln( "<info>Added field definition to Content Type Draft #$contentTypeDraft->id</info>" );
            $output->writeln( print_r( $contentTypeService->loadContentTypeDraft( $contentTypeId ), true ) );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            $output->writeln( "<error>" . $e->getMessage() . "</error>" );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\ForbiddenException $e )
        {
            $output->writeln( "<error>BLAH\n" . $e->getMessage() . "</error>" );
            print_r( $e );
        }
    }
}
