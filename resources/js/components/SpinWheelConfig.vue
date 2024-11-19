<template>
    <div class="spin-wheel-config">
        <h2 @click="toggleConfig">Cấu Hình Vòng Quay</h2>
        <div v-if="isConfigOpen" class="config-content">
            <p v-if="message" class="success-message">{{ message }}</p>

            <!-- Thêm collapse cho từng item -->
            <div v-for="(item, index) in items" :key="index" class="prize-config-item">
                <div @click="toggleCollapse(index)" class="collapse-header">
                    <span>{{ item.label }}</span>
                </div>
                <div v-if="collapsedItems[index]" class="collapse-content">
                    <label>Tên Giải Thưởng:</label>
                    <input type="text" v-model="item.label" :placeholder="`Giải Thưởng ${index + 1}`"/>

                    <label>Tỷ Lệ Trúng (Trọng Số):</label>
                    <input type="number" v-model="item.weight" min="0" placeholder="Trọng Số"/>

                    <label>Icon Giải Thưởng:</label>
                    <input type="file" @change="onIconChange($event, index)" accept="image/*"/>
                    <div v-if="item.icon" class="icon-preview">
                        <img :src="item.icon" alt="Icon Preview"/>
                    </div>

                    <label>Mã Màu Nền:</label>
                    <input type="color" v-model="item.backgroundColor"/>

                    <label>Hiển Thị:</label>
                    <select v-model="item.display_option">
                        <option value="icon">Icon</option>
                        <option value="text">Text</option>
                        <option value="both">Cả Hai</option>
                    </select>
                    <button @click="removeItem(index)">Xóa</button>
                </div>
            </div>

            <!-- Cấu hình chung -->
            <div class="config-section">
                <label>Thời Gian Quay (ms):</label>
                <input type="number" v-model="config.spinDuration" min="1000" placeholder="Thời Gian Quay"/>

                <label>Số Vòng Quay Trước Khi Dừng:</label>
                <input type="number" v-model="config.numberOfRevolutions" min="1" placeholder="Số Vòng Quay"/>

                <label>Giá Trị Giảm Tốc (rotationResistance):</label>
                <input type="number" v-model="config.rotationResistance" placeholder="Giá Trị Giảm Tốc"/>

                <label>Hàm Easing:</label>
                <select v-model="config.easingFunction">
                    <option value="cubicOut">cubicOut</option>
                    <option value="linear">linear</option>
                    <option value="quintOut">quintOut</option>
                </select>
            </div>

            <button @click="addItem">Thêm Giải Thưởng Mới</button>
            <button @click="saveSettings">Lưu Cấu Hình</button>
        </div>
    </div>
</template>

<script>
import {ref} from 'vue';

export default {
    emits: ['update:items', 'update:config'],
    setup(props, {emit}) {
        const items = ref([]);
        const config = ref({
            spinDuration: 4000,
            numberOfRevolutions: 20,
            easingFunction: 'cubicOut',
            rotationResistance: -35,
        });
        const message = ref('');
        const isConfigOpen = ref(false);

        // Tạo trạng thái collapse cho từng item
        const collapsedItems = ref([]);

        const toggleConfig = () => {
            isConfigOpen.value = !isConfigOpen.value;
        };

        const toggleCollapse = (index) => {
            collapsedItems.value[index] = !collapsedItems.value[index];
        };

        const loadConfig = async () => {
            const response = await fetch('/nova-vendor/spin-wheel-tool/config');
            const data = await response.json();
            items.value = data.prizes || [];
            config.value = data.config || {};
            emit('update:items', items.value);
            emit('update:config', config.value);
        };

        const saveSettings = async () => {
            message.value = 'Cấu hình đã được lưu thành công!';
            setTimeout(() => (message.value = ''), 3000);
            emit('update:items', items.value);
            emit('update:config', config.value);
        };

        const addItem = () => {
            const newItemId = items.value.length > 0 ? Math.max(...items.value.map((i) => i.id)) + 1 : 1;
            items.value.push({
                id: newItemId,
                label: `Giải Thưởng ${items.value.length + 1}`,
                weight: 1,
                display_option: 'both',
                icon: null,
                iconFile: null,
                backgroundColor: '#FFFFFF',
            });
        };

        const removeItem = (index) => {
            items.value.splice(index, 1);
        };

        const onIconChange = (event, index) => {
            const file = event.target.files[0];
            if (file) {
                items.value[index].iconFile = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    items.value[index].icon = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };

        loadConfig();

        return {
            items,
            config,
            message,
            isConfigOpen,
            toggleConfig,
            saveSettings,
            addItem,
            removeItem,
            onIconChange,
            collapsedItems,
            toggleCollapse,
        };
    },
};
</script>
