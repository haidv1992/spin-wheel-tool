<template>
  <div class="spin-wheel" :class="extraClass">
    <div class="wheel-container brand-glow-effect" ref="wheelContainer">
      <button class="spin-button" @click="handleSpinClick" :disabled="isSpinning || !canSpin"
              :aria-disabled="isSpinning || !canSpin">
        <img :src="centerIcon" alt="Nút Quay" />
      </button>
    </div>
  </div>
</template>

<script>
import {defineComponent, onMounted, ref} from 'vue';
import {Wheel} from 'spin-wheel';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import overlayImageSrc from '../../assets/bg-kim-1x.png';
import centerIconSrc from '../../assets/an.svg';

export default defineComponent({
  name: 'SpinWheel',
  props: {
    items: {
      type: Array,
      required: true,
    },
    config: {
      type: Object,
      required: true,
    },
    canSpin: {
      type: Boolean,
      required: true,
      default: false,
    },
    csrfToken: {
      type: String,
      required: true,
    },
    extraClass: {
      type: String,
      default: '',
    },
  },
  setup(props) {
    const pointerAngle = 135;
    const wheelContainer = ref(null);
    const wheel = ref(null);
    const isSpinning = ref(false);
    const currentPrizeIndex = ref(null);
    const imageElements = ref({});
    const overlayImage = new Image();
    overlayImage.src = overlayImageSrc;
    const centerIcon = centerIconSrc;

    // Preload hình ảnh cho các giải thưởng
    const preloadImages = async () => {
      const loadPromises = props.items.map((item) => {
        return new Promise((resolve) => {
          if (
              item.icon &&
              (item.display_option === 'icon' ||
                  item.display_option === 'both')
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

    // Khởi tạo vòng quay
    const initializeWheel = async () => {
      if (wheelContainer.value && !wheel.value) {
        await preloadImages();
        const wheelItems = props.items.map((item) => ({
          label:
              item.display_option === 'icon' ? '' : item.name,
          weight: 1, // Bạn có thể điều chỉnh trọng số nếu cần
          image: imageElements.value[item.id] || null,
          backgroundColor: item.backgroundColor || '#FFFFFF',
        }));

        wheel.value = new Wheel(wheelContainer.value, {
          items: wheelItems,
          pointerAngle,
          isInteractive: false,
          overlayImage: overlayImage,
        });

        // wheel.value.onRest = () => {
        //     if (currentPrizeIndex.value !== null) {
        //         const prize = props.items[currentPrizeIndex.value];
        //         Swal.fire({
        //             title: 'Chúc mừng!',
        //             text: `Bạn đã trúng giải: ${prize.name}`,
        //             icon: 'success',
        //             confirmButtonText: 'OK',
        //         });
        //         isSpinning.value = false;
        //         currentPrizeIndex.value = null;
        //     }
        // };
      }
    };

    /**
     * Xử lý hành động quay bằng cách giao tiếp với backend.
     */
    const spin = async () => {
      try {
        if (isSpinning.value || !props.canSpin) return;
        isSpinning.value = true;

        // Yêu cầu backend thực hiện quay
        const response = await fetch(
            '/nova-vendor/spin-wheel-tool/spin',
            {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken, // Bao gồm CSRF Token
                'Accept': 'application/json',
              },
              body: JSON.stringify({}),
              credentials: 'same-origin',
            }
        );

        if (!response.ok) {
          const errorData = await response.json();
          handleErrorResponse(response.status, errorData);
          return;
        }

        const data = await response.json();
        const {prize_id, prize_name, is_first_spin, spin_token} = data;

        const prizeIndex = props.items.findIndex(
            (item) => item.id === prize_id
        );

        if (wheel.value && prizeIndex >= 0) {
          currentPrizeIndex.value = prizeIndex;
          const duration = props.config.spinDuration;
          const numberOfRevolutions =
              props.config.numberOfRevolutions;
          const easingFunctions = {
            cubicOut: (t) => --t * t * t + 1,
            linear: (t) => t,
            quintOut: (t) => 1 + (--t) * t * t * t * t,
          };
          const easing =
              easingFunctions[props.config.easingFunction] ||
              easingFunctions['cubicOut'];

          wheel.value.spinToItem(
              prizeIndex,
              Number(duration),
              false,
              Number(numberOfRevolutions),
              1,
              easing
          );

          if (is_first_spin) {
            wheel.value.onRest = () => {
              promptUserInfo(spin_token, prize_name);
              isSpinning.value = false;
              currentPrizeIndex.value = null;
            };
          } else {
            wheel.value.onRest = () => {
              Swal.fire({
                title: 'Thành Công!',
                text: `Giải thưởng ${prize_name}`,
                icon: 'success',
                confirmButtonText: 'OK',
              });
              isSpinning.value = false;
              currentPrizeIndex.value = null;
            };
          }
        } else {
          Swal.fire({
            title: 'Xin lỗi!',
            text: 'Bạn đã đạt giới hạn quay.',
            icon: 'info',
            confirmButtonText: 'OK',
          });
          isSpinning.value = false;
        }
      } catch (error) {
        Swal.fire({
          title: 'Lỗi',
          text: error.message || 'Đã xảy ra lỗi khi quay vòng quay.',
          icon: 'error',
          confirmButtonText: 'OK',
        });
        isSpinning.value = false;
      }
    };


    /**
     * Xử lý các phản hồi lỗi khác nhau từ backend.
     * @param {number} status - Mã trạng thái HTTP.
     * @param {Object} errorData - Dữ liệu lỗi từ backend.
     */
    const handleErrorResponse = (status, errorData) => {
      if (status === 401) {
        Swal.fire({
          title: 'Không được phép',
          text: 'Vui lòng đăng nhập để tiếp tục.',
          icon: 'warning',
          confirmButtonText: 'Đăng Nhập',
        }).then(() => {
          window.location.href = '/login'; // Chuyển hướng đến trang đăng nhập
        });
      } else if (status === 429 && errorData.code === 'SPIN_LIMIT_REACHED') {
        Swal.fire({
          title: 'Đã Đạt Giới Hạn Quay',
          text: errorData.message || 'Bạn đã đạt giới hạn quay trong ngày hôm nay.',
          icon: 'info',
          confirmButtonText: 'Đặt Lịch Ngay',
        }).then((result) => {
          if (result.isConfirmed && result.value) {
            window.location.href = 'https://tgsuat.onelink.me/4jha/h6onylo2'; // Chuyển hướng đến trang đặt lịch
          }
        });
      } else {
        Swal.fire({
          title: 'Lỗi',
          text: errorData.message || 'Đã xảy ra lỗi khi quay vòng quay.',
          icon: 'error',
          confirmButtonText: 'OK',
        });
      }
      isSpinning.value = false;
    };

    /**
     * Yêu cầu người dùng nhập thông tin sau khi quay.
     * @param {string} spin_token - Token của lượt quay.
     * @param {string} prizeName - Tên giải thưởng đã trúng.
     */
    const promptUserInfo = (spin_token, prizeName) => {
      const modal = document.getElementById('modal-voucher-special');
      document.getElementById('prizeName').textContent = prizeName;
      openModal(modal);

      const submitButton = modal.querySelector('.submit-button');
      submitButton.addEventListener('click', function () {

        const name = document.getElementById('swal-input1').value.trim();
        const phone = document.getElementById('swal-input2').value.trim();
        const email = document.getElementById('swal-input3').value.trim();
        const note = document.getElementById('swal-input4').value.trim();

        if (!name || !phone || !email) {
          Swal.fire({
            icon: 'warning',
            title: 'Lỗi',
            text: 'Vui lòng điền đầy đủ tất cả các trường.',
            confirmButtonText: 'OK'
          });
          return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const phoneRegex = /^\d{10,15}$/;

        if (!emailRegex.test(email)) {
          Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: 'Định dạng email không hợp lệ.',
            confirmButtonText: 'OK'
          });
          return;
        }
        if (!phoneRegex.test(phone)) {
          Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: 'Số điện thoại không hợp lệ.',
            confirmButtonText: 'OK'
          });
          return;
        }

        const userData = {name, phone, email, note};
        submitCustomerInfo(userData, spin_token);

        closeModal(modal);
      });

      //     Swal.fire({
      //         title: 'Chúc Mừng!',
      //         html: `
      //     <p>Bạn đã trúng: <strong>${prizeName}</strong>. Vui lòng nhập thông tin để nhận giải thưởng.</p>
      //     <input id="swal-input1" class="swal2-input" placeholder="Tên Của Bạn">
      //     <input id="swal-input2" class="swal2-input" placeholder="Số Điện Thoại" type="tel">
      //     <input id="swal-input3" class="swal2-input" placeholder="Email" type="email">
      // `,
      //         focusConfirm: false,
      //         showCancelButton: true,
      //         confirmButtonText: 'Gửi',
      //         preConfirm: () => {
      //             const name = document.getElementById('swal-input1').value.trim();
      //             const phone = document.getElementById('swal-input2').value.trim();
      //             const email = document.getElementById('swal-input3').value.trim();

      //             if (!name || !phone || !email) {
      //                 Swal.showValidationMessage('Vui lòng điền đầy đủ tất cả các trường.');
      //                 return false;
      //             }

      //             // Xác thực email và số điện thoại đơn giản
      //             const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      //             const phoneRegex = /^\d{10,15}$/;
      //             if (!emailRegex.test(email)) {
      //                 Swal.showValidationMessage('Định dạng email không hợp lệ.');
      //                 return false;
      //             }
      //             if (!phoneRegex.test(phone)) {
      //                 Swal.showValidationMessage('Số điện thoại không hợp lệ.');
      //                 return false;
      //             }

      //             return { name, phone, email };
      //         },
      //     }).then((result) => {
      //         if (result.isConfirmed && result.value) {
      //             const userData = result.value;
      //             submitCustomerInfo(userData, spin_token);
      //         } else {
      //             // Tùy chọn xử lý khi hủy bỏ
      //         }
      //     });

    };


    /**
     * Gửi thông tin người dùng lên backend.
     * @param {Object} userData - Thông tin của người dùng.
     * @param {string} spinToken - Token của lượt quay.
     */
    const submitCustomerInfo = async (userData, spinToken) => {
      try {
        const response = await fetch(
            '/nova-vendor/spin-wheel-tool/submit-info',
            {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
                'Accept': 'application/json',
              },
              body: JSON.stringify({...userData, spin_token: spinToken}),
              credentials: 'same-origin', // Đảm bảo cookies được gửi cùng yêu cầu
            });

        if (!response.ok) {
          const errorData = await response.json();
          Swal.fire({
            title: 'Lỗi',
            text: errorData.message || 'Đã xảy ra lỗi khi gửi thông tin.',
            icon: 'error',
            confirmButtonText: 'OK',
          });
          return;
        }

        const data = await response.json();

        // Hiển thị Popup Chi Tiết Voucher
        Swal.fire({
          title: 'Voucher Của Bạn Đã Sẵn Sàng!',
          html: `
                <p>Mã Voucher: <strong>${data.voucher_code}</strong></p>
                <p>Ngày Hết Hạn: <strong>${data.voucher_expiry}</strong></p>
                <p>Vui lòng sử dụng ứng dụng Golden SpoonS để nhận giải thưởng của bạn.</p>
                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <button id="app-button" class="swal2-confirm swal2-styled">Nhận Mã Ngay!</button>
                    <button id="guide-button" class="swal2-cancel swal2-styled">Hướng Dẫn Lấy Voucher</button>
                </div>
            `,
          showConfirmButton: false,
          showCancelButton: false,
          didOpen: () => {
            // Gắn sự kiện cho các nút
            const appButton = Swal.getPopup().querySelector('#app-button');
            const guideButton = Swal.getPopup().querySelector('#guide-button');

            appButton.addEventListener('click', () => {
              window.location.href = 'https://apps.apple.com/app/golden-spoons/id123456789'; // Thay thế bằng link app thực tế
            });

            guideButton.addEventListener('click', () => {
              window.location.href = '/voucher-guide'; // Thay thế bằng trang hướng dẫn thực tế
            });
          },
        });
      } catch (error) {
        Swal.fire({
          title: 'Lỗi',
          text: error.message || 'Đã xảy ra lỗi khi gửi thông tin.',
          icon: 'error',
          confirmButtonText: 'OK',
        });
      }
    };


    // Xử lý sự kiện click nút quay
    const handleSpinClick = () => {
      if (props.canSpin) {
        spin();
      } else {
        // Hiển thị modal SweetAlert2 để thông báo người dùng không thể quay
        Swal.fire({
          title: 'Xin lỗi!',
          text: 'Bạn không có quyền quay vòng quay.',
          icon: 'warning',
          confirmButtonText: 'OK',
        });
      }
    };

    onMounted(async () => {
      await initializeWheel();
    });

    return {
      wheelContainer,
      centerIcon,
      isSpinning,
      canSpin: props.canSpin,
      handleSpinClick,
      extraClass: props.extraClass,
    };
  }
});
</script>

<style scoped>
.spin-wheel {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.wheel-container {
  position: relative;
  margin-bottom: 20px;
}

@media (max-width: 567px) {
  .wheel-container {
    width: 85vw;
    height: 85vw;
  }
}

@media (min-width: 568px) and (max-width: 1023px) {
  .wheel-container {
    width: 335px;
    height: 335px;
  }
}

@media (min-width: 1024px) {
  .wheel-container {
    width: 535px;
    height: 535px;
  }
}


.spin-button {
  position: absolute;
  top: 49%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 2;
  background-color: transparent;
  border: none;
  cursor: pointer;
}

@keyframes ping {
  0% {
    transform: scale(1);
    opacity: 1;
  }

  50% {
    transform: scale(1.2);
    opacity: 0.98;
  }

  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.spin-button img {
  width: 50px;
  height: auto;
  animation: ping 1.5s infinite;
  /* Continuous ping effect */
  transition: transform 0.3s ease;
  /* Hover scaling */
}

@media (max-width: 767px) {
  .spin-button img {
    width: 50px;
  }
}

@media (min-width: 768px) and (max-width: 1023px) {
  .spin-button img {
    width: 70px;
  }
}

@media (min-width: 1024px) {
  .spin-button img {
    width: 90px;
  }
}

.spin-button img:hover {
  transform: scale(1.3);
  /* Slightly larger scale on hover */
  animation: none;
  /* Disable ping effect during hover for smoother scaling */
}

@keyframes enhanced-glow {
  0% {
    box-shadow: 0 0 30px rgba(255, 0, 0, 0.6),
      /* Red */ 0 0 50px rgba(255, 215, 0, 0.4),
      /* Yellow */ 0 0 70px rgba(0, 191, 255, 0.3);
    /* Blue */
  }

  25% {
    box-shadow: 0 0 40px rgba(0, 191, 255, 0.6),
      /* Blue */ 0 0 60px rgba(0, 255, 127, 0.5),
      /* Green */ 0 0 80px rgba(255, 69, 0, 0.4);
    /* Orange */
  }

  50% {
    box-shadow: 0 0 50px rgba(255, 0, 0, 0.8),
      /* Red */ 0 0 70px rgba(255, 215, 0, 0.6),
      /* Yellow */ 0 0 90px rgba(0, 191, 255, 0.5);
    /* Blue */
  }

  75% {
    box-shadow: 0 0 40px rgba(0, 255, 127, 0.6),
      /* Green */ 0 0 60px rgba(255, 69, 0, 0.5),
      /* Orange */ 0 0 80px rgba(255, 215, 0, 0.4);
    /* Yellow */
  }

  100% {
    box-shadow: 0 0 30px rgba(255, 0, 0, 0.6),
      /* Red */ 0 0 50px rgba(255, 215, 0, 0.4),
      /* Yellow */ 0 0 70px rgba(0, 191, 255, 0.3);
    /* Blue */
  }
}

.brand-glow-effect {
  animation: enhanced-glow 1s infinite;
  /* Speed up the animation to 1s */
  border-radius: 50%;
  position: relative;
}
</style>
