<?php
/**
 * File containing the CreateContentTypeCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace EzSystems\CookbookBundle\Command;

use eZ\Publish\API\Repository\Exceptions\BadStateException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

class DeleteContentTypeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName( 'ezpublish:cookbook:delete_content_type' )->setDefinition(
            array(
                new InputArgument( 'identifier', InputArgument::REQUIRED, 'a content type identifier' )
            )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getContainer()->get( 'ezpublish.api.repository' );
        $contentTypeService = $repository->getContentTypeService();

        $repository->setCurrentUser( $repository->getUserService()->loadUser( 14 ) );

        $contentTypeIdentifier = $input->getArgument( 'identifier' );

        try
        {
            $contentType = $contentTypeService->loadContentType( $contentTypeIdentifier );
            $contentTypeService->deleteContentType( $contentType );
            $output->writeln( "<info>Content type $contentType->id was deleted" );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            $output->writeln( "<error>Content type with identifier  not found</error>" );
            return;
        }
        catch ( BadStateException $e )
        {
            $output->writeln( "<error>This content type can not be deleted as there are objects of this type</error>" );
            return;
        }
    }
}
