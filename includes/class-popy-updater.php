<?php
/**
 * Popy Auto-Updater
 *
 * Hooks into the WordPress plugin update system and checks the plugin's
 * GitHub Releases page for new versions. When a newer tag is found it
 * surfaces the update inside wp-admin → Plugins just like a repo update.
 *
 * Usage:
 *   new Popy_Updater( __FILE__, 'owner/repo', POPY_VERSION );
 *
 * The GitHub release MUST:
 *  - Use a semantic version tag (e.g. 1.2.0  or  v1.2.0).
 *  - Attach a ZIP asset named  popy-popup.zip  that contains the plugin
 *    folder.  If no asset is attached WordPress will fall back to the
 *    auto-generated source ZIP produced by GitHub.
 *
 * @package Popy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popy_Updater {

	/** @var string  Absolute path to the main plugin file. */
	private $plugin_file;

	/** @var string  Plugin slug, e.g. "popy-popup/popy-popup.php". */
	private $plugin_slug;

	/** @var string  GitHub "owner/repo". */
	private $github_repo;

	/** @var string  Current installed version. */
	private $current_version;

	/** @var string  Transient key for the cached API response. */
	private $transient_key;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_file     Absolute path to the main plugin file.
	 * @param string $github_repo     GitHub repository in "owner/repo" format.
	 * @param string $current_version Currently installed version string.
	 */
	public function __construct( $plugin_file, $github_repo, $current_version ) {
		$this->plugin_file     = $plugin_file;
		$this->plugin_slug     = plugin_basename( $plugin_file );
		$this->github_repo     = $github_repo;
		$this->current_version = $current_version;
		$this->transient_key   = 'popy_update_' . md5( $github_repo );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
		add_filter( 'plugins_api',                           array( $this, 'plugin_info' ), 10, 3 );
		add_filter( 'upgrader_post_install',                 array( $this, 'after_install' ), 10, 3 );
	}

	/* ── GitHub API ─────────────────────────────────────────────── */

	/**
	 * Fetch the latest release data from GitHub, with a 12-hour cache.
	 *
	 * @return object|false  Decoded release object, or false on failure.
	 */
	private function get_remote_release() {
		$cached = get_transient( $this->transient_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$url      = 'https://api.github.com/repos/' . $this->github_repo . '/releases/latest';
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept'     => 'application/vnd.github.v3+json',
					'User-Agent' => 'Popy-WordPress-Plugin/' . $this->current_version,
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			// Cache failure for 1 hour to avoid hammering the API.
			set_transient( $this->transient_key, false, HOUR_IN_SECONDS );
			return false;
		}

		$release = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $release->tag_name ) ) {
			set_transient( $this->transient_key, false, HOUR_IN_SECONDS );
			return false;
		}

		set_transient( $this->transient_key, $release, 12 * HOUR_IN_SECONDS );
		return $release;
	}

	/**
	 * Normalise a version tag: strip a leading "v" if present.
	 *
	 * @param  string $tag  e.g. "v1.2.0" or "1.2.0".
	 * @return string       e.g. "1.2.0".
	 */
	private function normalise_version( $tag ) {
		return ltrim( $tag, 'v' );
	}

	/**
	 * Return the download URL for the plugin ZIP.
	 *
	 * Prefers an asset named "popy-popup.zip"; falls back to the
	 * tarball / zipball URL that GitHub generates automatically.
	 *
	 * @param  object $release  GitHub release object.
	 * @return string
	 */
	private function get_download_url( $release ) {
		if ( ! empty( $release->assets ) ) {
			foreach ( $release->assets as $asset ) {
				if ( isset( $asset->name ) && 'popy-popup.zip' === $asset->name ) {
					return $asset->browser_download_url;
				}
			}
		}
		// Fallback: GitHub-generated ZIP of the tag.
		return ! empty( $release->zipball_url ) ? $release->zipball_url : '';
	}

	/* ── WordPress hooks ────────────────────────────────────────── */

	/**
	 * Inject update data into the WordPress update transient.
	 *
	 * @param  object $transient  WordPress update transient.
	 * @return object
	 */
	public function check_for_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$release = $this->get_remote_release();
		if ( ! $release ) {
			return $transient;
		}

		$remote_version = $this->normalise_version( $release->tag_name );

		if ( version_compare( $this->current_version, $remote_version, '<' ) ) {
			$transient->response[ $this->plugin_slug ] = (object) array(
				'slug'        => dirname( $this->plugin_slug ),
				'plugin'      => $this->plugin_slug,
				'new_version' => $remote_version,
				'url'         => 'https://github.com/' . $this->github_repo,
				'package'     => $this->get_download_url( $release ),
				'icons'       => array(),
				'banners'     => array(),
				'tested'      => '',
				'requires_php'=> '7.4',
			);
		}

		return $transient;
	}

	/**
	 * Supply plugin information shown in the "View version x.x.x details" modal.
	 *
	 * @param  false|object|array $result  Default result.
	 * @param  string             $action  Requested action.
	 * @param  object             $args    Request arguments.
	 * @return false|object
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}
		if ( empty( $args->slug ) || dirname( $this->plugin_slug ) !== $args->slug ) {
			return $result;
		}

		$release = $this->get_remote_release();
		if ( ! $release ) {
			return $result;
		}

		$remote_version = $this->normalise_version( $release->tag_name );

		return (object) array(
			'name'          => 'Popy – Simple WordPress Popups',
			'slug'          => dirname( $this->plugin_slug ),
			'version'       => $remote_version,
			'author'        => '<a href="https://wazidshah.com">Wazid Shah</a>',
			'homepage'      => 'https://github.com/' . $this->github_repo,
			'requires'      => '5.5',
			'requires_php'  => '7.4',
			'download_link' => $this->get_download_url( $release ),
			'sections'      => array(
				'description' => 'Timed Popups That Respect Your Visitors. Show a beautiful timed popup with cookie-based dismissal.',
				'changelog'   => ! empty( $release->body ) ? nl2br( esc_html( $release->body ) ) : 'See the <a href="https://github.com/' . esc_attr( $this->github_repo ) . '/releases">GitHub releases page</a> for full changelog.',
			),
		);
	}

	/**
	 * Rename the extracted folder to the correct plugin folder name after install.
	 *
	 * GitHub ZIP files extract to a folder named "owner-repo-{hash}" which
	 * breaks the plugin if WordPress can't find it under the expected slug.
	 *
	 * @param  bool  $response    Install response.
	 * @param  array $hook_extra  Extra data.
	 * @param  array $result      Install result.
	 * @return array
	 */
	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem;

		if ( empty( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_slug ) {
			return $result;
		}

		$plugin_folder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname( $this->plugin_slug );

		if ( $wp_filesystem->exists( $result['destination'] ) ) {
			$wp_filesystem->move( $result['destination'], $plugin_folder, true );
		}

		$result['destination']         = $plugin_folder;
		$result['destination_name']    = dirname( $this->plugin_slug );

		// Re-activate the plugin after update.
		activate_plugin( $this->plugin_slug );

		return $result;
	}
}
