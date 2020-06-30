export function transformForSelect(items) {
	items.forEach(item => {
		item.label = item.name;
		item.value = item.id;
	});

	return items;
}

export function transformForTree(items) {
	items.forEach(item => {
		item.label = item.name;

		if (item.children) {
			item.children = transformForTree(item.children);
		}
	});

	return items;
}

export function transformDirectoriesForTree(items, showChecklist = true) {
	const result = [];

	items.forEach(item => {
		const newItem = Object.assign({}, item.object);
		newItem.parentId = item.parentId;
		newItem.objectType = item.objectType;

		if ((item.objectType === 'checklist' && showChecklist) || item.objectType === 'directory') {
			result.push(newItem);
		}
	});

	result.forEach(item => {
		item.children = [];
	});

	return result;
}

export function transformUsersForSelect(items) {
	items.forEach(item => {
		item.label = `${item.lastName}, ${item.firstName}`;
		item.value = item.id;
	});

	return items;
}

export function transformCompaniesForSelect(items) {
	items.forEach(item => {
		item.label = item.name;
		item.value = { name: item.name, sector: item.sector };
	});

	return items;
}
