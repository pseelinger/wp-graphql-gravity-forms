<?php
namespace WPGraphQL\GF\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\GF\Data\Factory;
use WPGraphQL\GF\Data\Loader\FormsLoader;
use WPGraphQL\GF\Type\Enum\FormFieldTypeEnum;
use WPGraphQL\GF\Type\WPInterface\FormField;
use WPGraphQL\GF\Utils\Compat;

class FormFieldsRootQueryConnection extends AbstractConnection {
    /**
     * GraphQL field name in node tree.
     *
     * @var string
     */
    public static $from_field_name = 'formFields';

    public static function register(): void
    {
        register_graphql_connection(
            Compat::resolve_graphql_config(
                [
                    'fromType'       => 'RootQuery',
                    'toType'         => FormField::$type,
                    'fromFieldName'  => 'formFields',
                    'connectionArgs' => self::get_connection_args(),
                    'resolve'        => static function ( $root, array $args, AppContext $context, ResolveInfo $info ) {
                        if (isset($args['where']['form'])) {
                            $form = $context->get_loader( FormsLoader::$name )->load( (int) $args['where']['form'] );
                            Compat::set_app_context( $context, 'gfForm', $form );
                            return Factory::resolve_form_fields_connection( $form, $args, $context, $info );
                        }
                        return null;
                    },
                ]
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function get_connection_args(): array {
        return [
            'form' => [
                'type' => 'Int',
                'description' => static fn () => __('The Gravity Forms Form ID to retrieve fields.', 'wp-graphql-gravtiy-forms')
            ],
            'ids'         => [
                'type'        => [ 'list_of' => 'ID' ],
                'description' => static fn () => __( 'Array of form field IDs to return.', 'wp-graphql-gravity-forms' ),
            ],
            'adminLabels' => [
                'type'        => [ 'list_of' => 'String' ],
                'description' => static fn () => __( 'Array of form field adminLabels to return.', 'wp-graphql-gravity-forms' ),
            ],
            'fieldTypes'  => [
                'type'        => [ 'list_of' => FormFieldTypeEnum::$type ],
                'description' => static fn () => __( 'Array of Gravity Forms Field types to return.', 'wp-graphql-gravity-forms' ),
            ],
            'pageNumber'  => [
                'type'        => 'Int',
                'description' => static fn () => __( 'The form page number to return.', 'wp-graphql-gravity-forms' ),
            ],
        ];
    }
}