<?php

namespace Smartemailing;

use FluentForm\App\Http\Controllers\IntegrationManagerController;
use FluentForm\Framework\Foundation\Application;
use FluentForm\Framework\Helpers\ArrayHelper;
use smartemailing\Integrations;


class FluentFormIntegration extends IntegrationManagerController {

	public function __construct( Application $app ) {
		parent::__construct(
			$app,
			'SmartEmailing',
			'smartemailing',
			'_fluentform_smartemailing_settings',
			'fluentform_smartemailing_feed',
			16
		);

		$this->logo = fluentFormMix( 'img/integrations/sendinblue.png' );

		$this->description = 'Create contacts easily on your Brevo (formerly SendInBlue) email list with Fluent Forms Brevo integration.';

		$this->registerAdminHooks();

	}

	public function getIntegrationDefaults( $settings, $formId ) {
		return [
			'name'                  => '',
			'list_id'               => '',
			'fieldEmailAddress'     => '',
			'custom_field_mappings' => (object) [],
			'default_fields'        => (object) [],
			'other_fields_mapping'  => [
				[
					'item_value' => '',
					'label'      => ''
				]
			],
			'ip_address'            => '{ip}',
			'conditionals'          => [
				'conditions' => [],
				'status'     => false,
				'type'       => 'all'
			],
			'enabled'               => true
		];
	}


	public function pushIntegration( $integrations, $formId ) {
		$integrations[ $this->integrationKey ] = [
			'title'                 => $this->title . ' Integration',
			'logo'                  => $this->logo,
			'is_active'             => $this->isConfigured(),
			'configure_title'       => __( 'Configuration required!', 'smartemailing' ),
			'global_configure_url'  => admin_url( 'admin.php?page=fluent_forms_settings#general-smartemailing-settings' ),
			'configure_message'     => __( 'Smart Emailing  is not configured yet! Please configure your Smart Emailing API first', 'smartemailing' ),
			'configure_button_text' => __( 'Set Brevo API', 'smartemailing' )
		];

		return $integrations;
	}

	public function getSettingsFields( $settings, $formId ) {
		return [
			'fields'              => [
				[
					'key'         => 'name',
					'label'       => __( 'Name', 'smartemailing' ),
					'required'    => true,
					'placeholder' => __( 'Your Feed Name', 'smartemailing' ),
					'component'   => 'text'
				],
				[
					'key'         => 'list_id',
					'label'       => __( 'Smart Emailing  Segment', 'smartemailing' ),
					'placeholder' => __( 'Select Smart Emailing  Segment', 'smartemailing' ),
					'tips'        => __( 'Select the Smart Emailing segment you would like to add your contacts to.', 'smartemailing' ),
					'component'   => 'list_ajax_options',
					'options'     => $this->getLists()
				],
				[
					'key'                => 'custom_field_mappings',
					'require_list'       => true,
					'label'              => __( 'Map Fields', 'smartemailing' ),
					'tips'               => __( 'Select which Fluent Forms fields pair with their<br /> respective Smart Emailing  fields.', 'smartemailing' ),
					'component'          => 'map_fields',
					'field_label_remote' => __( 'Smart Emailing Field', 'smartemailing' ),
					'field_label_local'  => __( 'Form Field', 'smartemailing' ),
					'primary_fileds'     => [
						[
							'key'           => 'fieldEmailAddress',
							'label'         => __( 'Email Address', 'smartemailing' ),
							'required'      => true,
							'input_options' => 'emails'
						]
					],
					'default_fields'     => [
						array(
							'name'     => 'first_name',
							'label'    => esc_html__( 'First Name', 'smartemailing' ),
							'required' => false
						),
						array(
							'name'     => 'last_name',
							'label'    => esc_html__( 'Last Name', 'smartemailing' ),
							'required' => false
						)
					]
				],
				[
					'key'                => 'other_fields_mapping',
					'require_list'       => true,
					'label'              => __( 'Other Fields', 'smartemailing' ),
					'tips'               => __( 'Select which Fluent Forms fields pair with their<br /> respective Brevo fields.', 'smartemailing' ),
					'component'          => 'dropdown_many_fields',
					'field_label_remote' => __( 'Smart Emailing  Field', 'smartemailing' ),
					'field_label_local'  => __( 'Form Field', 'smartemailing' ),
					'options'            => $this->attributes()
				],
				[
					'require_list' => true,
					'key'          => 'conditionals',
					'label'        => __( 'Conditional Logics', 'smartemailing' ),
					'tips'         => __( 'Allow Smart Emailing  integration conditionally based on your submission values', 'smartemailing' ),
					'component'    => 'conditional_block'
				],
				[
					'require_list'   => true,
					'key'            => 'enabled',
					'label'          => __( 'Status', 'smartemailing' ),
					'component'      => 'checkbox-single',
					'checkbox_label' => __( 'Enable This feed', 'smartemailing' )
				]
			],
			'button_require_list' => true,
			'integration_title'   => $this->title
		];
	}

	public function getGlobalFields( $fields ) {
		return [
			'logo'             => $this->logo,
			'menu_title'       => __( 'Smartemailing API Settings', 'smartemailing' ),
			'menu_description' => __( '', 'smartemailing' ),
			'valid_message'    => __( 'Your Smartemailing configuration is valid', 'smartemailing' ),
			'invalid_message'  => __( 'Your Smartemailing configuration is invalid', 'smartemailing' ),
			'save_button_text' => __( 'Save Settings', 'smartemailing' ),
			'fields'           => [
				'apiKey' => [
					'type'        => 'password',
					'placeholder' => __( 'Smartemailing API Key', 'smartemailing' ),
					'label_tips'  => __( "Enter your Smartemailing API Key", 'smartemailing' ),
					'label'       => __( 'Smartemailing V3 API Key', 'smartemailing' ),
				],

				'username' => [
					'type'        => 'text',
					'placeholder' => __( 'Smartemailing username', 'smartemailing' ),
					'label_tips'  => __( "Enter your Smartemailing username", 'smartemailing' ),
					'label'       => __( 'Smartemailing username', 'smartemailing' ),
				]
			],
			'hide_on_valid'    => true,
			'discard_settings' => [
				'section_description' => __( 'Your Smartemailing integration is up and running', 'smartemailing' ),
				'button_text'         => __( 'Disconnect Smartemailing', 'smartemailing' ),
				'data'                => [
					'apiKey'   => '',
					'username' => ''
				],
				'show_verify'         => true
			]
		];
	}

	public function getMergeFields( $list, $listId, $formId ) {
		return [];
	}

	public function getGlobalSettings( $settings ) {
		$globalSettings = get_option( $this->optionKey );
		if ( ! $globalSettings ) {
			$globalSettings = [];
		}
		$defaults = [
			'apiKey'   => '',
			'username' => '',
			'status'   => ''
		];

		return wp_parse_args( $globalSettings, $defaults );
	}

	public function saveGlobalSettings( $settings ) {
		if ( ! $settings['apiKey'] && ! $settings['username'] ) {
			$integrationSettings = [
				'apiKey'   => '',
				'username' => '',
				'status'   => false
			];

			// Update the details with siteKey & secretKey.
			update_option( $this->optionKey, $integrationSettings, 'no' );

			wp_send_json_success( [
				'message' => __( 'Your settings has been updated and discarded', 'smartemailing' ),
				'status'  => false
			], 200 );
		}

		try {
			$settings['status'] = false;
			update_option( $this->optionKey, $settings, 'no' );
			$api  = new Api( $settings['apiKey'], $settings['username'] );
			$auth = $api->auth_test();
			if ( isset( $auth['account_id'] ) ) {
				$settings['status'] = true;
				update_option( $this->optionKey, $settings, 'no' );
				wp_send_json_success( [
					'status'  => true,
					'message' => __( 'Your settings has been updated!', 'smartemailing' )
				], 200 );
			}
			throw new \Exception( __( 'Invalid Credentials', 'smartemailing' ), 400 );
		} catch ( \Exception $e ) {
			wp_send_json_error( [
				'status'  => false,
				'message' => $e->getMessage()
			], $e->getCode() );
		}
	}


	protected function getLists() {
		$api = $this->getApiClient();
		if ( ! $api ) {
			return [];
		}

		$lists = $api->getLists();

		if ( ! $lists ) {
			return [];
		}

		$formattedLists = [];
		foreach ( $lists as $list ) {
			if ( is_array( $list ) ) {
				$formattedLists[ strval( $list['id'] ) ] = $list['name'];
			}
		}

		return $formattedLists;
	}

	protected function getApiClient() {
		$settings = get_option( $this->optionKey );

		return new Api(
			$settings['apiKey'],
			$settings['username']
		);
	}


	/*
     * Submission Broadcast Handler
     */

	public function notify( $feed, $formData, $entry, $form ) {
		$feedData = $feed['processedValues'];
		if ( ! is_email( $feedData['fieldEmailAddress'] ) ) {
			$feedData['fieldEmailAddress'] = ArrayHelper::get( $formData, $feedData['fieldEmailAddress'] );
		}

		if ( ! is_email( $feedData['fieldEmailAddress'] ) ) {
			do_action( 'fluentform/integration_action_result', $feed, 'failed', __( 'SmartEmailing API call has been skipped because no valid email available', 'smartemailing' ) );

			return;
		}

		$addData = [];

		$addData['contactlists'] = [
			[
				'id'     => absint( $feedData['list_id'] ),
				'status' => 'confirmed'

			]
		];
		$addData['emailaddress'] = $feedData['fieldEmailAddress'];
		$attributes              = [];

		$defaultFields = ArrayHelper::get( $feedData, 'default_fields' );

		if ( isset( $defaultFields['first_name'] ) ) {
			$addData['name'] = $defaultFields['first_name'];
		}
		if ( isset( $defaultFields['last_name'] ) ) {
			$addData['surname'] = $defaultFields['last_name'];
		}

		foreach ( ArrayHelper::get( $feedData, 'other_fields_mapping' ) as $item ) {
			$id = $item['label'];
			if ( $item['item_value'] ) {
				$attributes[] = [
				   'value' => $item['item_value'],
					'id'    => $id
				];
			}
		}

		$addData['customfields'] = $attributes;

		$addData = apply_filters_deprecated(
			'fluentform_integration_data_' . $this->integrationKey,
			[
				$addData,
				$feed,
				$entry
			],
			FLUENTFORM_FRAMEWORK_UPGRADE,
			'fluentform/integration_data_' . $this->integrationKey,
			'Use fluentform/integration_data_' . $this->integrationKey . ' instead of fluentform_integration_data_' . $this->integrationKey
		);

		$addData = apply_filters( 'fluentform/integration_data_' . $this->integrationKey, $addData, $feed, $entry );

		$api = $this->getApiClient();

		$response = $api->addContact( $addData );

		if ( ! is_wp_error( $response ) && ! empty( $response['id'] ) ) {
			do_action( 'fluentform/integration_action_result', $feed, 'success', __( 'SmartEmailing feed has been successfully initialed and pushed data', 'smartemailing' ) );
		} else {
			$error = __( 'API Error when submitting Data', 'smartemailing' );
			if ( is_wp_error( $response ) ) {
				$error = $response->get_error_message();
			}
			if ( is_array( $error ) ) {
				$error = $response->get_error_messages()[0];
			}
			do_action( 'fluentform/integration_action_result', $feed, 'failed', $error );
		}
	}


	public function attributes() {
		$api = $this->getApiClient();
		if ( ! $api ) {
			return [];
		}

		$attributes = $api->attributes();

		$formattedAttributes = [];
		foreach ( $attributes["data"] as $attribute ) {
			if ( is_array( $attribute ) ) {
				$formattedAttributes[ $attribute['id'] ] = $attribute['name'];
			}
		}

		return $formattedAttributes;
	}
}
