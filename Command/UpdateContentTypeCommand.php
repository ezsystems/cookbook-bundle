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

class UpdateContentTypeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:update_content_type_draft' )->setDefinition(
            array(
                new InputArgument( 'id', InputArgument::REQUIRED, 'a content type draft id' ),
                new InputOption( 'identifier', null, InputOption::VALUE_OPTIONAL, 'Updated identifier' ),
                new InputOption( 'name', null, InputOption::VALUE_OPTIONAL, 'Updated name (in eng-GB)' ),
                new InputOption( 'description', null, InputOption::VALUE_OPTIONAL, 'Updated description (in eng-GB)' ),
                new InputOption( 'nameSchema', null, InputOption::VALUE_OPTIONAL, 'New name schema. Example: <title>' ),
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
        $contentTypeUpdateStruct = $contentTypeService->newContentTypeUpdateStruct();
        $contentTypeUpdateStruct->mainLanguageCode = 'eng-GB';

        // We set the Content Type naming pattern to the title's value
        if ( $input->hasOption( 'nameSchema' ) )
        {
            $contentTypeUpdateStruct->nameSchema = $input->getOption( 'nameSchema' );
        }

        // set names for the content type
        if ( $input->hasOption( 'name' ) )
        {
            $contentTypeUpdateStruct->names = array( 'eng-GB' => $input->getOption( 'name' ) );
        }

        // set description for the content type
        if ( $input->hasOption( 'description' ) )
        {
            $contentTypeUpdateStruct->descriptions = array( 'eng-GB' => $input->getOption( 'description' ) );
        }

        try
        {
            $contentTypeService->updateContentTypeDraft(
                $contentTypeDraft, $contentTypeUpdateStruct
            );
            $output->writeln( "<info>Content type $contentTypeDraft->id was updated</info>" );
            $output->writeln( print_r( $contentTypeService->loadContentTypeDraft( $contentTypeId ), true ) );
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
