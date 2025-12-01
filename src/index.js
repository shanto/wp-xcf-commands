import { useState, useMemo, useEffect } from "@wordpress/element";
import { AsyncModeProvider, useSelect } from "@wordpress/data";
import { store as coreDataStore } from "@wordpress/core-data";
import { useCommandLoader } from "@wordpress/commands";
import { registerPlugin } from "@wordpress/plugins";
import { addQueryArgs } from "@wordpress/url";
import { page as pageIcon } from "@wordpress/icons";

function useDebouncedValue(value, delay) {
	const [debounced, setDebounced] = useState(value);

	useEffect(() => {
		const handle = setTimeout(() => setDebounced(value), delay);
		return () => clearTimeout(handle);
	}, [value, delay]);

	return debounced;
}

function ContentTypeCommandLoader({ type: postType, search }) {
	const debouncedSearch = useDebouncedValue(search, 750);
	var args = ["postType", postType.slug];
	var query = useMemo(
		() => ({
			search: debouncedSearch || undefined,
			per_page: 10,
			orderby: "relevance",
		}),
		[debouncedSearch],
	);

	const { records, isLoading } = useSelect(
		(select) => {
			const core = select(coreDataStore);
			const isResolving = core.isResolving(...args, query);
			var results = !isResolving
				? core.getEntityRecords(...args, query)
				: null;
			return {
				records: results,
				isLoading: isResolving,
			};
		},
		[postType.slug, query],
	);

	const commands = useMemo(() => {
		if (!records) {
			return [];
		}
		return records.map((record) => {
			return {
				name: `cpt-commands/edit-${postType.slug}-${record.id}`,
				icon: pageIcon,
				label: (record.title && record.title.rendered) || "(No title)",
				description: postType.labels?.singular_name || postType.name,
				callback: function callback(_ref2) {
					var close = _ref2.close;

					var args = {
						action: "edit",
						post: record.id,
					};

					document.location = addQueryArgs("post.php", args);
					close();
				},
			};
		});
	}, [records, postType.slug, postType.labels?.singular_name, postType.name]);

	useEffect(
		function () {
			if (!search || !commands) {
				return;
			}

			setTimeout(() => {
				document.querySelector("input[cmdk-input]")?.dispatchEvent(
					new KeyboardEvent("keydown", {
						key: "Home",
						code: "Home",
						keyCode: 36,
						which: 36,
						bubbles: true,
					}),
				);
			}, 200);

		},
		[search, commands],
	);

	return { commands, isLoading };
}

function ContentTypeSearch({ postType }) {
	useCommandLoader({
		name: `cpt-commands/${postType.slug}-search`,
		hook: (search) => {
			return ContentTypeCommandLoader({ ...search, type: postType });
		},
		context: 'site-editor'
	});
}

function ContentSearchRoot() {
	var postTypes;

	postTypes = useSelect(function (select) {
		var allPostTypes = select(coreDataStore).getPostTypes({
			per_page: 20,
		});

		if (!allPostTypes) {
			return;
		}

		return allPostTypes.filter(function (postType) {
			if (
				!postType ||
				!postType?.supports?.editor ||
				postType?.slug.match(/^boldblocks_|^acf-|^wp_/) ||
				!postType?.visibility?.show_ui ||
				["post", "page", "navigation", "block"].includes(postType.slug)
			) {
				return false;
			}

			return true;
		});
	});

	if (postTypes) {
		return (
			<AsyncModeProvider value={true}>
				{postTypes.map((postType) => (
					<ContentTypeSearch
						postType={postType}
						key={postType.slug}
					/>
				))}
			</AsyncModeProvider>
		);
	} else {
		return null;
	}
}

registerPlugin("cpt-commands-root", {
	render: ContentSearchRoot,
});
