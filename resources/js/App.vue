<template>
   <router-view />
</template>

<script setup>
</script>

<style>
@media (max-width: 768px) {
   .p-toast {
      left: 10px !important;
      right: 10px !important;
      transform: none !important;
      width: auto !important;
      max-width: calc(100vw - 20px);
   }
}
</style>