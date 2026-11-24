<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const isGuestView = computed(() => route.meta.guestOnly === true)
</script>

<template>
  <div id="ekaa-app" class="font-sans antialiased text-slate-900 bg-slate-50 min-h-screen">
    <router-view v-if="isGuestView" />
    <AppLayout v-else>
      <router-view />
    </AppLayout>
  </div>
</template>
