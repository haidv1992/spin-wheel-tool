import SpinWheelSettings from './pages/SpinWheelSettings.vue';

Nova.booting((app, store) => {
  Nova.inertia('SpinWheelTool', SpinWheelSettings);
});
