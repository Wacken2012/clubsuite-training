<!--
© 2026 Stefan Schulz – Alle Rechte vorbehalten.
-->
<template>
  <div class="talk-button-container">
    <template v-if="talkRoom">
      <a
        :href="talkRoom.room_url"
        target="_blank"
        rel="noopener noreferrer"
        class="talk-link-button"
        :title="'Chat zum Termin öffnen: ' + eventTitle"
      >
        <span class="material-design-icon">💬</span>
        Talk-Chat öffnen
      </a>
    </template>
    <template v-else>
      <div class="talk-loading" v-if="isCreating">
        <span class="spinner">⏳</span>
        Talk-Raum wird erstellt…
      </div>
      <div class="talk-error" v-else-if="error">
        ❌ {{ error }}
      </div>
      <div class="talk-unavailable" v-else>
        📵 Kein Talk-Raum verfügbar
      </div>
    </template>
  </div>
</template>

<script>
export default {
  name: 'TalkButton',
  props: {
    talkRoom: {
      type: Object,
      default: null,
    },
    eventTitle: {
      type: String,
      default: 'Training Event',
    },
    isCreating: {
      type: Boolean,
      default: false,
    },
    error: {
      type: String,
      default: null,
    },
  },
};
</script>

<style scoped>
.talk-button-container {
  margin: 12px 0;
  display: flex;
  align-items: center;
}

.talk-link-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background-color: #0082c9;
  color: white;
  border-radius: 4px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s ease;
  cursor: pointer;
}

.talk-link-button:hover {
  background-color: #006ba6;
  box-shadow: 0 2px 8px rgba(0, 130, 201, 0.3);
}

.talk-link-button:active {
  transform: scale(0.98);
}

.material-design-icon {
  font-size: 18px;
}

.talk-loading,
.talk-error,
.talk-unavailable {
  padding: 10px 16px;
  border-radius: 4px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

.talk-loading {
  background-color: #fff3cd;
  color: #856404;
}

.talk-error {
  background-color: #f8d7da;
  color: #721c24;
}

.talk-unavailable {
  background-color: #e9ecef;
  color: #383d41;
}

.spinner {
  display: inline-block;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
