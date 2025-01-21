<?php
/*
 * Plugin Name: Event Sticker Widget
 * Plugin URI: http://www.smitka.net/event-sticker
 * Description: Show Events as Side Stickers
 * Version: 1.2
 * Author: Ivan Smitka
 * Author URI: http://www.smitka.net
 *
 * The MIT License
 *
 * Copyright 2025 Web4People Ivan Smitka <ivan at stimulus dot cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 */

/**
 * @author Ivan Smitka <ivan at stimulus dot cz>
 */
class WP_EventSticker {

	const UPDATE_URI = "https://www.smitka.net/wp-plugin/wp-event-sticker";
	const EVENT_STICKER_PRIORITY = "EventSticker_Priority";
	const EVENT_STICKER_DAYS_BEFORE = "EventSticker_DaysBefore";
	const EVENT_STICKER_URL = "URL_Turnaj";


	const CUSTOM_ATTRS = [
		self::EVENT_STICKER_PRIORITY    => null,
		self::EVENT_STICKER_DAYS_BEFORE => null,
		self::EVENT_STICKER_URL         => null,
	];

	/**
	 */
	public static function init() {
		// Scripts
		if ( ! is_admin() ) { // show only in public area
			add_action( 'wp_enqueue_scripts', [
				'WP_EventSticker',
				'enqueue_scripts'
			] );
			add_action( 'wp_footer', [
				'WP_EventSticker',
				'render'
			] );
		} else {
			//add admin action hook
			add_filter( 'update_plugins_www.smitka.net', function ( $update, $plugin_data, $plugin_file, $locales ) {
				if ( $plugin_file == plugin_basename( __FILE__ ) ) {
					$update = self::getUpdate( $plugin_data['UpdateURI'] );
				}

				return $update;
			}, 10, 4 );
			add_filter( 'plugins_api', function ( $res, $action, $args ) {
				if ( 'plugin_information' !== $action ) {
					return $res;
				}
				if ( plugin_basename( __DIR__ ) !== $args->slug ) {
					return $res;
				}

				$update             = self::getUpdate( self::UPDATE_URI );
				$res                = json_decode( json_encode( $update ), false );
				$res->sections      = $update["sections"];
				$res->download_link = $update["package"];

				return $res;

			}, 9999, 3 );

			if ( $_SERVER["REQUEST_METHOD"] === "GET"
			     && in_array( "post_type", array_keys( $_GET ) )
			     && $_GET["post_type"] === "event"
			     && in_array( $_SERVER["DOCUMENT_URI"], [
					"/wp-admin/post-new.php",
					"/wp-admin/edit.php"
				] ) ) {
				if ( $eventAttrs = em_get_attributes() ) {
					$add    = false;
					$names  = $eventAttrs["names"];
					$values = $eventAttrs["values"];
					foreach ( self::CUSTOM_ATTRS as $customAttr => $options ) {
						if ( ! in_array( $customAttr, $names ) ) {
							$names[]               = $customAttr;
							$values[ $customAttr ] = $options;
							$add                   = true;
						}
					}
					if ( $add ) {
						update_option( "dbem_placeholders_custom", implode( "\n", array_map( function ( $name ) use ( $values ) {
							$value = $values[ $name ];

							return "#_ATT{{$name}}" . ( empty( $value ) ? "" : "{{$value}}" );
						}, $names ) ) );
					}
				}
			}
		}
	}

	/**
	 * @param $update_URI
	 *
	 * @return mixed
	 */
	private static function getUpdate( $update_URI ) {
		$request      = wp_remote_get( $update_URI );
		$request_body = wp_remote_retrieve_body( $request );
		$update       = json_decode( $request_body, true );

		return $update;
	}

	/**
	 */
	public static function enqueue_scripts() {
		foreach ( scandir( __DIR__ . "/dist/assets" ) as $path ) {
			$pathInfo = pathinfo( $path );
			if ( strpos( $pathInfo["filename"], "index" ) === 0 ) {
				wp_enqueue_style( 'wp-event-sticker', plugins_url( '/static/event-sticker.css', __FILE__ ) );
				wp_enqueue_script( 'wp-event-sticker', plugins_url( "/dist/assets/{$path}", __FILE__ ), [ 'jquery' ] );
				break;
			}
		}
	}

	/**
	 * @param string $place
	 * @param EM_DateTime $eventStart
	 *
	 */
	public static function render( $args = array() ): void {
		$self = new self();
		print "<section class='EventSticker' style='display: none;' data-event-stickers>\n";
		foreach ( $self->getData( 14 ) as $event ) {
			$title = explode( " ", $event->event_name );
			$place = null;
			$logo  = null;
			if ( count( $title ) > 2 ) {
				$title = implode( " ", array_slice( $title, 0, 2 ) );
			} else {
				$title = $event->name;
			}
			if ( $location = $event->get_location() ) {
				$place = $location->location_name;
			}
			$content = array_filter( [
				$event->start()->format( "j. n. Y" ),
				$title,
				$place
			] );
			$signUp  = array_key_exists( self::EVENT_STICKER_URL, $event->event_attributes ) ? $event->event_attributes[ self::EVENT_STICKER_URL ] : null;
			foreach ( $event->get_categories() as $category ) {
				/**
				 * @var \EM_Category $category
				 */
				if ( $slug = $category->slug ) {
					$logo = "<img src='/wp-content/uploads/category/{$slug}.png' alt='{$category->name}'>";
				}
			} ?>
            <div class="Event" data-id="<?= $event->id ?>">
                <a href="#" class="close"><i class="fas fa-times"></i></a>
                <div class="logo">
					<?= $logo ?>
                </div>
                <div class="content">
					<?= implode( "<br>", $content ) ?>
                </div>
                <div class="extension">
					<?php if ( $signUp ) { ?><a href="<?= $signUp ?>" class="link">Přihlásit se</a><?php } ?>
                </div>
            </div>
			<?php
		}
		print "</section>\n";
	}

	/**
	 * @param $daysBefore
	 *
	 * @return \EM_Event[]
	 */
	final public function getData( $daysBefore ): array {
		if ( class_exists( "EM_Events" ) ) {
			if ( is_array( $events = \EM_Events::get() ) ) {

				// filter events by attributes
				$events = array_filter( $events, function ( $event ) use ( $daysBefore ) {
					if ( $event instanceof \EM_Event ) {
						if ( $event->is_published() && is_array( $attrs = $event->event_attributes ) ) {
							$matchPriority    = ! empty( array_filter( $attrs, function ( $value, $key ) {
								return $key === self::EVENT_STICKER_PRIORITY && $value > 0;
							}, ARRAY_FILTER_USE_BOTH ) );
							$daysBeforeCustom = $daysBefore;
							foreach (
								array_filter( $attrs, function ( $value, $key ) {
									return $key === self::EVENT_STICKER_DAYS_BEFORE && $value > 0;
								}, ARRAY_FILTER_USE_BOTH ) as $value
							) {
								$daysBeforeCustom = $value;
							}
							$matchStart = $event->start > time() && $event->start < ( time() + $daysBeforeCustom * 24 * 60 * 60 );

							return $matchPriority && $matchStart;
						}
					}

					return false;
				} );

				usort( $events, function ( $a, $b ) {
					return $a->start - $b->start;
				} );

				// group by priority
				$usedPriority = [];
				$events       = array_filter( $events, function ( $event ) use ( &$usedPriority ) {
					$eventPriority = $event->event_attributes[ self::EVENT_STICKER_PRIORITY ];
					if ( in_array( $eventPriority, $usedPriority ) ) {
						return false;
					}
					$usedPriority[] = $eventPriority;

					return true;
				} );

				return $events;
			}
		}

		return [];
	}
}

add_action( 'plugins_loaded', array(
	'WP_EventSticker',
	'init'
), 100 );
