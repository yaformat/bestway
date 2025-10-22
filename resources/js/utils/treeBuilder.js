/**
 * Построение дерева категорий с фильтрацией по type
 *
 * @param {Array} items - плоский массив категорий
 * @param {String|null} type - тип категорий (если нужно фильтровать)
 * @returns {Array} дерево
 */
export function buildCategoryTree(items, type = null) {
  const tree = []
  const lookup = {}

  const filteredItems = items.filter(item => {
    return type ? item.type === type : true
  })

  filteredItems.forEach(item => {
    lookup[item.id] = {
      ...item,
      children: [],
      parent_id: item.parent?.id ?? 0,
      level: 0,
    }
  })

  filteredItems.forEach(item => {
    const current = lookup[item.id]
    const parentId = item.parent?.id ?? 0

    if (parentId && lookup[parentId]) {
      const parent = lookup[parentId]
      current.level = parent.level + 1
      parent.children.push(current)
    } else {
      current.level = 0
      tree.push(current)
    }
  })

  return tree
}

/**
 * Преобразует дерево категорий в плоский список с отступами, с возможностью исключения и добавления root
 *
 * @param {Array} tree - дерево категорий
 * @param {Object} options
 * @param {Number|String|null} [options.excludeId=null] - ID категории, которую нужно исключить
 * @param {String|null} [options.root=null] - если указано, добавляет элемент с id: 0 и именем = root
 * @param {Number|null} [options.maxLevel=1] - максимальный уровень вложенности (0 = только корень)
 * @param {String} [options.indentChar='—'] - символ отступа
 * @returns {Array} плоский список с отступами
 */
export function flattenTreeWithIndent(
  tree,
  { root = null, maxLevel = 1, excludeId = null, indentChar = '—' } = {}
) {
  const result = []

  if (typeof root === 'string') {
    result.push({
      id: 0,
      name: root,
    })
  }

  function traverse(nodes, level = 0) {
    for (const node of nodes) {
      if (node.id === excludeId) continue

      result.push({
        id: node.id,
        name: `${indentChar.repeat(level)} ${node.name}`,
      })

      if (node.children?.length && (maxLevel === null || level < maxLevel)) {
        traverse(node.children, level + 1)
      }
    }
  }

  traverse(tree)
  return result
}
