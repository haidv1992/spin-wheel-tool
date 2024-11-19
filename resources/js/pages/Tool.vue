<template>
  <div class="spin-wheel-container">
    <SpinWheel ref="spinWheel" />
    <button @click="spin">Spin the Wheel!</button>
    <p>{{ message }}</p>
  </div>
</template>

<script>
import SpinWheel from '../components/SpinWheel.vue';

export default {
  components: { SpinWheel },
  data() {
    return {
      message: 'Good Luck!',
      spinning: false,
      prize: null,
    };
  },
  methods: {
    async spin() {
      if (this.spinning) return;

      this.spinning = true;
      this.$refs.spinWheel.startInfiniteSpin();

      try {
        const response = await fetch('/spin-wheel/frontend/spin', { method: 'POST' });
        const data = await response.json();
        this.prize = data.prize;
        this.message = `You won: ${this.prize}!`;

        this.$refs.spinWheel.stopAtPrize(this.prize);
      } catch (error) {
        console.error('Error spinning the wheel:', error);
        this.message = 'An error occurred. Please try again!';
      } finally {
        this.spinning = false;
      }
    }
  }
};
</script>
<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap');

.spin-wheel-settings {
  font-family: 'Rubik', sans-serif;
  color: #fff;
  text-align: center;
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
  background-color: #1a1a1a;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

.wheel-container {
  width: 300px;
  height: 300px;
  margin: 20px auto;
  border: 5px solid #007bff;
  border-radius: 50%;
  background-color: #333;
  position: relative;
  overflow: hidden;
}

.controls, .prize-list {
  margin-top: 20px;
}

.controls select, .controls button {
  margin-top: 10px;
  padding: 10px;
  font-size: 16px;
}

.prize-config {
  display: flex;
  gap: 10px;
  justify-content: center;
  align-items: center;
  margin-bottom: 10px;
  color: #ddd;
}

.prize-config input[type="text"],
.prize-config input[type="number"] {
  padding: 8px;
  border: 1px solid #007bff;
  border-radius: 4px;
  background-color: #2a2a2a;
  color: #fff;
  width: 100px;
}

button {
  padding: 8px 16px;
  margin-top: 10px;
  cursor: pointer;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  transition: background-color 0.3s;
}

button:hover {
  background-color: #0056b3;
}

.success-message {
  margin-top: 20px;
  color: green;
  font-weight: bold;
}
</style>
