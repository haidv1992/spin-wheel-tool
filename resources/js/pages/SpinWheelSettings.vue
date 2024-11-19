<template>
  <div class="spin-wheel-settings">
    <div class="left-panel">
      <div class="wheel-container" ref="wheelContainer">
        <button class="spin-button" @click="spin" :disabled="isSpinning || !canSpin">
          <img :src="centerIcon" alt="Center Icon"/>
        </button>
<!--        <div class="pointer">-->
<!--          <img :src="pointerIcon" alt="pointer Icon"/>-->
<!--        </div>-->
      </div>
    </div>
    <div class="right-panel prize-config">
      <h2>Cấu Hình Vòng Quay</h2>

      <div v-for="(item, index) in items" :key="index">
        <label @click="item.isOpen = !item.isOpen" class="collapsible-label">
          <span>{{ item.label }}</span>
          <span>{{ item.isOpen ? '[-]' : '[+]' }}</span> <!-- Show plus or minus sign -->
        </label>
        <div v-show="item.isOpen"  :class="['collapsible-content', { open: item.isOpen }]">
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
<!--            <option value="both">Cả Hai</option>-->
          </select>
          <button @click="removeItem(index)" class="remove-button">Xóa</button>
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

      <div class="sticky-buttons">
        <p v-if="message" class="success-message">{{ message }}</p>
        <button @click="addItem">Thêm Giải Thưởng Mới</button>
        <button @click="saveSettings">Lưu Cấu Hình</button>
      </div>

    </div>
  </div>
</template>


<script>
import {onMounted, ref, watch} from 'vue';
import {Wheel} from 'spin-wheel';
import bgImage from '../../assets/bg.png';
import centerIcon from '../../assets/an.svg';
import pointerIcon from '../../assets/kim.svg';
import bgWheelImageSrc from '../../assets/bg-kim-1x.png';

export default {
  setup() {
    const pointerAngle = 135;
    const wheelContainer = ref(null);
    const wheel = ref(null);
    const items = ref([]);
    const message = ref('');
    const isSpinning = ref(false);
    const currentPrizeIndex = ref(null);
    const canSpin = ref(true);
    const imageElements = ref({});
    const overlayImage = new Image();
    overlayImage.src = bgWheelImageSrc;

    // const btnImage = new Image();
    // btnImage.src = centerIcon;

    const config = ref({
      spinDuration: 4000,
      numberOfRevolutions: 20,
      easingFunction: 'cubicOut',
        rotationResistance: -35,

    });

    onMounted(async () => {
      await loadConfig();
      await initializeWheel();
      await checkCanSpin();
    });

    const checkCanSpin = async () => {
      try {
        const response = await Nova.request().get(
            '/nova-vendor/spin-wheel-tool/check-spin'
        );
        canSpin.value = response.data.can_spin;

        if (!canSpin.value) {
          message.value = 'Bạn đã hết lượt quay.';
        }
        return response.data.can_spin
      } catch (error) {
        console.error('Không thể kiểm tra quyền quay:', error);
      }
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

    const loadConfig = async () => {
      try {
        const response = await Nova.request().get(
            '/nova-vendor/spin-wheel-tool/config'
        );
        const prizes = response.data.prizes || [];
        items.value = prizes.map((prize) => ({
          id: prize.id,
          label: prize.name,
          icon: prize.icon || null,
          display_option: prize.display_option || 'both',
          weight: prize.weight || 1,
          backgroundColor: prize.backgroundColor || '#FFFFFF',
        }));
        config.value = {
          spinDuration: Number(response.data.config.spinDuration || 4000),
          numberOfRevolutions: Number(response.data.config.numberOfRevolutions || 20),
          easingFunction: response.data.config.easingFunction || 'cubicOut',
            rotationResistance: response.data.config.rotationResistance || -35,

        };
      } catch (error) {
        console.error('Không thể tải cấu hình:', error);
      }
    };

    const saveSettings = async () => {
      try {
        const formData = new FormData();
        items.value.forEach((item, index) => {
          formData.append(`prizes[${index}][id]`, item.id || index + 1);
          formData.append(`prizes[${index}][name]`, item.label);
          formData.append(`prizes[${index}][weight]`, item.weight ?? 0);
          formData.append(`prizes[${index}][backgroundColor]`, item.backgroundColor || '#FFFFFF');
          formData.append(
              `prizes[${index}][display_option]`,
              item.display_option || 'both'
          );
          if (item.iconFile) {
            formData.append(
                `icons[${item.id || index + 1}]`,
                item.iconFile
            );
          }
        });
        Object.entries(config.value).forEach(([key, value]) => {
          formData.append(`config[${key}]`, value);
        });

        await Nova.request().post(
            '/nova-vendor/spin-wheel-tool/admin/save',
            formData,
            {
              headers: {
                'Content-Type': 'multipart/form-data',
              },
            }
        );
        message.value = 'Cấu hình đã được lưu thành công!';
        setTimeout(() => (message.value = ''), 3000);
      } catch (error) {
        console.error('Không thể lưu cấu hình:', error);
      }
    };

    const spin = async () => {
      try {
        if (isSpinning.value || !canSpin.value) return;
        isSpinning.value = true;

        // Kiểm tra quyền quay
        const checkResponse = await checkCanSpin();
        if (!checkResponse) {
          message.value = 'Bạn đã hết lượt quay.';
          isSpinning.value = false;
          canSpin.value = false;
          return;
        }

        const response = await Nova.request().post(
            '/nova-vendor/spin-wheel-tool/spin'
        );
        const prizeId = response.data.prize_id;
        const prizeIndex = items.value.findIndex(
            (item) => item.id === prizeId
        );

        if (wheel.value && prizeIndex >= 0) {
          currentPrizeIndex.value = prizeIndex;
          const duration = config.value.spinDuration;
          const numberOfRevolutions = config.value.numberOfRevolutions;
          const easingFunctions = {
            cubicOut: (t) => --t * t * t + 1,
            linear: (t) => t,
            quintOut: (t) => 1 + (--t) * t * t * t * t,
          };
          const easing =
              easingFunctions[config.value.easingFunction] ||
              easingFunctions['cubicOut'];

          // Sử dụng spinToItem
          wheel.value.spinToItem(prizeIndex, duration, false, numberOfRevolutions, 1, easing);

          // Đặt sự kiện onRest
          wheel.value.onRest = () => {
            if (currentPrizeIndex.value !== null) {
              const prize = items.value[currentPrizeIndex.value];
              message.value = `Kết quả: ${prize.label}`;
              isSpinning.value = false;
              currentPrizeIndex.value = null;
              checkCanSpin();
            }
          };
        } else {
          console.error('Không thể quay vì chỉ số không hợp lệ.');
          isSpinning.value = false;
        }
      } catch (error) {
        console.error('Không thể quay:', error);
        isSpinning.value = false;
      }
    };


    const updateWheelItems = async () => {
      await preloadImages();
      if (wheel.value) {
        const wheelItems = items.value.map((item) => ({
          label: item.display_option === 'icon' ? '' : item.label,
          image: item.display_option === 'text' ? null : imageElements.value[item.id] || null,
          weight: 1,
          backgroundColor: item.backgroundColor || '#FFFFFF',
        }));
        wheel.value.init({
          items: wheelItems,
          pointerAngle,
          isInteractive: false,
        });

        wheel.value.onRest = () => {
          console.log('Vòng quay dừng lại');
          if (currentPrizeIndex.value !== null) {
            const prize = items.value[currentPrizeIndex.value];
            message.value = `Kết quả: ${prize.label}`;
            isSpinning.value = false;
            currentPrizeIndex.value = null;
            checkCanSpin();
          }
        };
      }
    };


    watch(
        items,
        () => {
          if (!isSpinning.value) {
            updateWheelItems();
          }
        },
        {deep: true}
    );

    const initializeWheel = async () => {
      if (wheelContainer.value && !wheel.value) {
        await preloadImages();
        const wheelItems = items.value.map((item) => ({
          label: item.display_option === 'icon' ? '' : item.label,
          weight: 1,
          image: imageElements.value[item.id] || null,
          backgroundColor: item.backgroundColor || '#FFFFFF',
        }));
        wheel.value = new Wheel(wheelContainer.value, {
          items: wheelItems,
          pointerAngle,
          isInteractive: false,
          // image: btnImage,
          overlayImage: overlayImage,
        });
        // Thiết lập sự kiện onRest
        wheel.value.onRest = () => {
          if (currentPrizeIndex.value !== null) {
            const prize = items.value[currentPrizeIndex.value];
            message.value = `Kết quả: ${prize.label}`;
            isSpinning.value = false;
            currentPrizeIndex.value = null;
            checkCanSpin();
          }
        };
      }
    };


    const preloadImages = async () => {
      const loadPromises = items.value.map((item) => {
        return new Promise((resolve) => {
          if (
              item.icon &&
              (item.display_option === 'icon' || item.display_option === 'both')
          ) {
            const img = new Image();
            img.src = item.icon;
            img.onload = () => {
              imageElements.value[item.id] = img;
              resolve();
            };
            img.onerror = () => resolve();
          } else {
            resolve();
          }
        });
      });
      await Promise.all(loadPromises);
    };


    const addItem = () => {
      const newItemId =
          items.value.length > 0
              ? Math.max(...items.value.map((i) => i.id)) + 1
              : 1;
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

    return {
      wheelContainer,
      centerIcon,
      pointerIcon,
      bgImage,
      config,
      items,
      spin,
      isSpinning,
      canSpin,
      saveSettings,
      addItem,
      removeItem,
      message,
      onIconChange,
      initializeWheel,
      updateWheelItems,
    };
  },
};
</script>


<style scoped>
.spin-wheel-settings {
  display: flex;
  align-items: flex-start;
  padding: 20px;
  color: #333;
  font-family: Arial, sans-serif;
}

.left-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-right: 20px;
  position: relative;
}

.wheel-container {
  top: 100px;
  position: relative;
  width: 350px;
  height: 350px;
  margin-bottom: 20px;
}

.right-panel {
  flex: 1;
  max-width: 300px;
  background-color: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  max-height: 70vh;
  overflow-y: auto;
}

.prize-config {
  display: flex;
  flex-direction: column;
  margin-bottom: 15px;
}

.prize-config label {
  font-weight: bold;
  margin-bottom: 5px;
}

.prize-config input[type="text"],
.prize-config select,
.prize-config input[type="number"] {
  padding: 8px;
  border: 1px solid #007bff;
  border-radius: 4px;
  //background-color: #2a2a2a;
  //color: #fff;
  margin-bottom: 10px;
  width: 100%;
}

button {
  padding: 10px;
  border: none;
  border-radius: 4px;
  background-color: #007bff;
  color: #fff;
  cursor: pointer;
  margin-top: 10px;
}
.spin-button{
  background-color:transparent;
}
.success-message {
  margin-top: 20px;
  color: green;
  font-weight: bold;
}

.icon-preview img {
  max-width: 100px;
  max-height: 100px;
}

.spin-button {
  position: absolute;
  top: 46%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 2;
  color: #fff;
  border: none;
  border-radius: 50%;
  cursor: pointer;
}

.spin-button img {
  width: 50px;
  height: auto;
  transition: transform 0.3s ease;
}

.spin-button img:hover {
  transform: scale(1.04);
}

.pointer img {
  width: 30px;
  height: auto;
  transform: rotate(-15deg);
}

.left-panel {
  height: 600px;
  background-image: url("/images/bg.png");
  background-repeat: no-repeat;
  background-size: cover;
  background-position: top;
}
label {
  cursor: pointer;
}

label span {
  font-weight: bold;
  margin-left: 5px;
}

div.v-show {
  margin-top: 10px;
}
.collapsible-label {
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  padding: 10px;
  background-color: #f0f0f0;
  border-radius: 4px;
  margin-top: 10px;
  font-weight: bold;
  color: #333;
  transition: background-color 0.3s ease;
}

.collapsible-label:hover {
  background-color: #e0e0e0;
}

.collapsible-content {
  overflow: hidden;
  max-height: 0;
  transition: max-height 0.3s ease, padding 0.3s ease;
  padding: 0 10px;
  margin-top: 5px;
  background-color: #f9f9f9;
  border-radius: 4px;
}

.collapsible-content input,
.collapsible-content select,
.collapsible-content button {
  margin-top: 8px;
  width: 100%;
}

.collapsible-content.open {
  max-height: 500px; /* Adjust as needed */
  padding: 10px;
}

.icon-preview img {
  max-width: 50px;
  max-height: 50px;
  border-radius: 4px;
  margin-top: 8px;
}

.remove-button {
  background-color: #ff4d4f;
  color: white;
  border: none;
  padding: 8px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.remove-button:hover {
  background-color: #ff7875;
}

.sticky-buttons {
  position: sticky;
  bottom: -15px;
  left: 0;
  background-color: #f8f9fa;
  padding: 10px;
  border-top: 2px solid #ddd;
  text-align: center;
}
.sticky-buttons button {
  width: calc(100% - 20px);
  margin: 5px auto;
}
.config-section{
  padding-bottom: 50px;
}
</style>
