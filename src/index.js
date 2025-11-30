import { useMemo, useEffect } from "@wordpress/element";
import { useSelect } from "@wordpress/data";
import { store as coreDataStore } from "@wordpress/core-data";
import { useCommandLoader } from "@wordpress/commands";
import { registerPlugin } from "@wordpress/plugins";
import { addQueryArgs } from "@wordpress/url";
import { page as pageIcon } from "@wordpress/icons";

function useContentSearchCommandLoader(_ref) {
	var search = _ref.search;
	var preLoading = false;

	var _useSelect = useSelect(
		function (select) {

			const stillLoading = {
				results: [],
				isLoading: true,
			};

			if (preLoading) {
				return stillLoading;
			}

			preLoading = true;
			
			var allPostTypes = select( coreDataStore ).getPostTypes( { per_page: 20 } ) || [];

			preLoading = false;

			var postTypes = allPostTypes.filter(function (postType) {
				if (
					!postType ||
					!postType?.supports?.editor ||
					postType?.slug.match(/^boldblocks_|^acf-|^wp_/) ||
					!postType?.visibility?.show_ui || ['post', 'page', 'navigation', 'block'].includes(postType.slug)
				) {
					return false;
				}

				return true;
			});

			if (!search || search?.length < 3) {
				return stillLoading;
			}

			var query = {
				search: search,
				per_page: 10,
				orderby: "relevance",
			};

			var results = postTypes.map(function (postType) {
				return {
					postType: postType,
					records:
						select(coreDataStore).getEntityRecords("postType", postType.slug, query) || [],
					isLoading: !select(coreDataStore).hasFinishedResolution(
						"getEntityRecords",
						"postType",
						postType.slug,
						query,
					),
				};
			});

			var isLoading = results.some(function (item) {
				return item.isLoading;
			});

			return {
				results: results,
				isLoading: isLoading,
			};
		},
		[search],
	),
	results = _useSelect.results,
	isLoading = _useSelect.isLoading;

	var commands = useMemo(
		function () {
			var all = [];

			results.forEach(function (item) {
				var postType = item.postType;
				var labels = postType.labels || {};

				(item.records || []).forEach(function (record) {
					all.push({
						name:
							"xcf-commands/edit-" +
							postType.slug +
							"-" +
							record.id,
						icon: pageIcon,
						label:
							(record.title && record.title.rendered) ||
							"(No title)",
						description:
							labels.singular_name || postType.slug,
						callback: function callback(_ref2) {
							var close = _ref2.close;

							var args = {
								action: 'edit',
								post: record.id,
							};

							document.location = addQueryArgs("post.php", args);
							close();
						},
					});
				});
			});

			return all.slice(0, 10);
		},
		[results, history],
	);

	useEffect(
		function () {
			if (!search || !commands || !commands.length) {
				return;
			}

			const firstItem = document
				.querySelector("[cmdk-group-items]>*:first-child");
			firstItem?.scrollIntoView();
			firstItem?.focus({ preventScroll: true });
		},
		[search, commands && commands.length],
	);

	return {
		commands: commands,
		isLoading: isLoading,
	};
}

function ContentSearchCommandLoader() {
	useCommandLoader({
		name: "xcf-commands/content-search",
		hook: useContentSearchCommandLoader,
	});

	return null;
}

registerPlugin("xcf-commands-content-search", {
	render: ContentSearchCommandLoader,
});

