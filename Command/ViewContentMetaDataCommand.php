<?php
/**
 * File containing the ViewContentMetaDataCommand class.
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
class ViewContentMetaDataCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezp_cookbook:viewcontentmetadata' )->setDefinition(
                array(
                        new InputArgument( 'contentId', InputArgument::REQUIRED, 'An existing content id' )
                )
        );
    }

    /**
     * returns a version status as string
     * @param int $status
     * @return string
     */
    private function outputStatus( $status ) {
        switch( $status )
        {
            case 0: return 'DRAFT';
            case 1: return 'PUBLISHED';
            case 2: return 'ARCHIVED';
        }
        return "UNKNOWN";
    }


    /**
     * returns relation type as string
     * @param int $relationType
     * @return string
     */
    private function outputRelationType( $relationType ) {
        switch( $relationType )
        {
            case 1: return 'COMMON';
            case 2: return 'EMBED';
            case 4: return 'LINK';
            case 8: return 'ATTRIBUTE';
        }
        return "UNKNOWN";
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
        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();
        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        // load the admin user and set it has current user in the repository
        $user = $userService->loadUser( 14 );
        $repository->setCurrentUser( $user );

        try
        {
            $contentInfo = $contentService->loadContentInfo( $contentId );

            // show all locations of the content
            $locations = $locationService->loadLocations( $contentInfo );
            $output->writeln( "Locations:" );
            foreach( $locations as $location )
            {
                $output->write( "  Location: $location->pathString " ); // the path build from id's

                // printout the url alias
                $urlAlias = $urlAliasService->reverseLookup( $location );
                $output->writeln( " URLAlias: $urlAlias->path" );
            }

            // show all relations of the current version
            $versionInfo = $contentService->loadVersionInfo( $contentInfo );
            $relations = $contentService->loadRelations( $versionInfo );
            $output->writeln( "Relations:" );
            foreach( $relations as $relation )
            {
                $name = $relation->destinationContentInfo->name;
                $output->write( "  Relation of type ");
                $output->write( $this->outputRelationType( $relation->type ) );
                $output->writeln( " to content $name" );
            }

            // show meta data
            $output->writeln( "Name: $contentInfo->name" );
            $output->writeln( "Type: " .$contentInfo->contentType->identifier );
            $output->writeln( "Last modified: " . $contentInfo->modificationDate->format('Y-m-d') );
            $output->writeln( "Published: ". $contentInfo->publishedDate->format('Y-m-d') );
            $output->writeln( "RemoteId: $contentInfo->remoteId" );
            $output->writeln( "Main Language: $contentInfo->mainLanguageCode" );
            $output->writeln( "Always avaialble: " . ($contentInfo->alwaysAvailable==1?'Yes':'No' ) );

            $owner = $userService->loadUser( $contentInfo->ownerId );
            $output->writeln( "Owner: " . $owner->contentInfo->name );

            $section = $sectionService->loadSection( $contentInfo->sectionId );
            $output->writeln( "Section: $section->name" );

            // show versions
            $versionInfos = $contentService->loadVersions( $contentInfo );
            foreach ( $versionInfos as $versionInfo )
            {
                $creator = $userService->loadUser( $versionInfo->creatorId );
                $output->write( "Version $versionInfo->versionNo: '" );
                $output->write( $creator->contentInfo->name );
                $output->writeln( "' " . $this->outputStatus( $versionInfo->status ) . " " . $versionInfo->initialLanguageCode);
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

