<template>
  <div class="table-item-name-with-img d-flex align-center">
    <div class="table-img">
      <div class="table-img-fit">
        <img v-if="photoUrl" v-lazy="photoUrl" />
      </div>
    </div>
    <div class="text-sm ps-2">
      <ResourceInfoDialog 
        ref="resourceDialog"
        :id="entityId" 
        :name="displayName" 
        :allowOpen="allowOpen"
        :historyId="historyId"
        :stockId="stockId"
        :showTrigger="true"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps } from 'vue';

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  historyId: {
    type: Number,
    default: null
  },
  stockId: {
    type: Number,
    default: null
  }
});

const resourceDialog = ref(null);

const allowOpen = computed(() => {
  let deleted = props.item.raw ? props.item.deleted_at : props.item.deleted_at;
  return (deleted) ? false : true;
});

const entityId = computed(() => {
  return props.item.raw ? props.item.id : props.item.id;
});

const displayName = computed(() => {
  return props.item.raw ? props.item.name : props.item.name;
});

const photoUrl = computed(() => {
  return props.item.raw ? props.item.photo?.url : props.item.photo?.url;
});
</script>

<style scoped>
.table-item-name-with-img {
  position: relative;
}

.table-img {
  display: block;
  width: 50px;
  min-width: 50px;
  height: 0;
  padding-top: 50px;
  position: relative;
  overflow: hidden;
  cursor: pointer;
}

.table-img-fit {
  display: block;
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  background-image: url(data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxOTkxLjI3IDE5MjEuMzMiPjxkZWZzPjxzdHlsZT4uY2xzLTF7ZmlsbDojZmZmO2ZpbGwtb3BhY2l0eTowfS5jbHMtMntmaWxsOnNpbHZlcn08L3N0eWxlPjwvZGVmcz48dGl0bGU+cGxhY2Vob2xkZXIgaWNvbjwvdGl0bGU+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMCAwaDE5OTEuMjd2MTkyMS4zM0gweiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTEyODguNTIgNzg3LjUxbC0zNC4yLTE5OS45LTcwMC4xNSAxMjIuOTMgMTAyLjYxIDU4NC43MSA2OS40OS0xMS43NnY1MC4yNGg3MTAuODNWNzg3LjUxem0tNTYyLjI1IDQzNy4xOGwtMjIuNDUgNC4zMS04My4zOC00NzEuNCA1ODYuODQtMTAzLjY5IDIzLjUyIDEzMy42Mkg3MjYuMjd6bTY1My4xMSA1MS4zMUg3ODRWODQ1LjIyaDU5NS40eiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTgyMi40NiAxMjMwLjA4aDUxOC40NHYtMTcwLjIybC0xMDktMTIzLjZhMjggMjggMCAwIDAtNDQuNzQgMy41OGwtOTMuOSAxNDguODJhMTguMjcgMTguMjcgMCAwIDEtMjcuNzMgMy43OGwtNjEuMDItNTUuNDRhMjUuMjggMjUuMjggMCAwIDAtMzcuMDcgMy4zN3oiLz48Y2lyY2xlIGNsYXNzPSJjbHMtMiIgY3g9Ijg5Mi4xNiIgY3k9Ijk0OC4yIiByPSI0MC44MSIvPjwvc3ZnPg==);
  background-position: center center;
  background-repeat: no-repeat;
}

.table-img-fit img {
  object-fit: cover;
  position: relative;
  height: 100%;
  width: 100%;
  background: #fff;
}
.table-img-fit img[lazy=error] {
  background: #fff4f4;
}
.table-img-fit img[lazy=loading] {
  opacity:0;
}
.table-img-fit img[lazy=loaded] {
  opacity:1;
  transition:all 0.2s;
}
.dialog-img {
  width: 100%;
  height: auto;
}
</style>
