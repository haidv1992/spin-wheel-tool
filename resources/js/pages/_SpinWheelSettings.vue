<template>
  <div class="spin-wheel-settings">
    <SpinWheel :items="items" :config="config" />
    <SpinWheelConfig @update:items="updateItems" @update:config="updateConfig" />
  </div>
</template>

<script>
import SpinWheel from '../components/SpinWheel.vue';
import SpinWheelConfig from '../components/SpinWheelConfig.vue';
import {ref} from 'vue';

export default {
  components: {
    SpinWheel,
    SpinWheelConfig,
  },
  setup() {
    const items = ref([]);
    const config = ref({});

    const updateItems = (newItems) => {
      items.value = newItems;
    };

    const updateConfig = (newConfig) => {
      config.value = newConfig;
    };

    return {
      items,
      config,
      updateItems,
      updateConfig,
    };
  },
};
</script>
<style scoped>
.spin-wheel-settings {
  display: flex;
  align-items: flex-start;
  padding: 20px;
}

</style>