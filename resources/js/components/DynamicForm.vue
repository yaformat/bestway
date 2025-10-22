<!-- src/components/DynamicForm.vue -->
<template>
  <div>
    <h3>Filters</h3>
    <div v-for="filter in filters" :key="filter.id">
      <label :for="filter.id">{{ filter.name }}</label>
      <div v-if="filter.type === 'presence'">
        <input type="checkbox" :id="filter.id" v-model="filter.selected_values" @change="updateFilters" />
      </div>
      <div v-else-if="filter.type === 'list'">
        <select :id="filter.id" v-model="filter.selected_values" @change="updateFilters">
          <option v-for="value in filter.values" :key="value.id" :value="value.id">{{ value.name }}</option>
        </select>
      </div>
      <div v-else-if="filter.type === 'nested_list'">
        <select :id="filter.id" v-model="filter.selected_values" @change="updateFilters">
          <option v-for="value in filter.values" :key="value.id" :value="value.id">{{ value.name }}</option>
        </select>
      </div>
      <!-- Add more types as needed -->
    </div>

    <h3>Sortings</h3>
    <div v-for="(sorting, key) in sortings" :key="key">
      <label :for="key">{{ sorting.name }}</label>
      <select :id="key" v-model="sorting.selected" @change="updateSortings">
        <option value="asc">Ascending</option>
        <option value="desc">Descending</option>
      </select>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    filters: {
      type: Array,
      required: true
    },
    sortings: {
      type: Object,
      required: true
    }
  },
  methods: {
    updateFilters() {
      this.$emit('update:filters', this.filters);
    },
    updateSortings() {
      this.$emit('update:sortings', this.sortings);
    }
  }
};
</script>
