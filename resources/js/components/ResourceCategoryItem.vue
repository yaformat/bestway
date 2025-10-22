<template>
  <div>
    <div
      class="table-row"
    >
      <div class="table-cell">
          <div
            class="tree-item-row"
            :class="{ 'is-last': node.isLast }"
            :style="{ '--level': node.level }"
          >
            <div class="tree-lines"></div>
            <div class="tree-content">
              <span>{{ node.name }}</span>
            </div>
          </div>
      </div>
      <div class="table-cell">
        <span class="text-sm">{{ node.resources_count }}</span>
      </div>
      <div class="table-cell">
        <div class="actions" v-if="isTrashedView">
            <VBtn @click="$emit('restore', node.id)" size="small" color="secondary" variant="outlined">
              <VIcon icon="mdi-restore" />
            </VBtn>
        </div>
        <div class="actions" v-else>
            <VBtn @click="$emit('edit', node)" size="small" variant="outlined">
              <VIcon icon="mdi-pencil-outline" />
            </VBtn>
            <VBtn @click="$emit('archive', node.id)" size="small" variant="outlined">
              <VIcon icon="mdi-archive-outline" />
            </VBtn>
        </div>
      </div>
    </div>
     
    <div v-for="category in node.children" :key="category.id">
      <ResourceCategoryItem
        :node="category"
        :isTrashedView="isTrashedView"
        @edit="$emit('edit', $event)"
        @archive="$emit('archive', $event)"
        @restore="$emit('restore', $event)"
      />
    </div>

  </div>
</template>

<script setup>

const props = defineProps({
  node: Object,
  isTrashedView: Boolean
})

const emit = defineEmits(['edit', 'archive', 'restore'])

</script>
