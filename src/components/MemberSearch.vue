<template>
  <div class="member-search-container">
    <div class="search-input-wrapper">
      <input
        v-model="searchQuery"
        class="form-control"
        placeholder="Mitglied suchen..."
        @input="onSearchInput"
        @focus="showDropdown = true"
        @blur="closeDropdownDelayed"
      />
      <span v-if="loading" class="loading-spinner">⟳</span>
    </div>

    <div v-if="showDropdown && (searchResults.length > 0 || searchQuery)" class="dropdown-results">
      <div v-if="loading" class="loading-message">Suche läuft...</div>
      <div v-else-if="searchResults.length > 0" class="results-list">
        <div
          v-for="member in searchResults"
          :key="member.id"
          class="result-item"
          @click="selectMember(member)"
          :title="`ID: ${member.id}, Nummer: ${member.memberNumber}`"
        >
          <span class="member-name">{{ member.firstname }} {{ member.lastname }}</span>
          <span class="member-info">{{ member.memberNumber }}</span>
        </div>
      </div>
      <div v-else-if="searchQuery && !loading" class="no-results">
        Keine Mitglieder gefunden
      </div>
    </div>

    <div v-if="selectedMember" class="selected-member">
      <span class="selected-name">✓ {{ selectedMember.firstname }} {{ selectedMember.lastname }}</span>
      <button @click="clearSelection" class="clear-btn">✕</button>
    </div>
  </div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
  name: 'MemberSearch',
  props: {
    value: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      searchQuery: '',
      searchResults: [],
      selectedMember: null,
      loading: false,
      showDropdown: false,
      debounceTimer: null
    }
  },
  watch: {
    value(newVal) {
      if (newVal) {
        this.selectedMember = newVal
        this.searchQuery = `${newVal.firstname} ${newVal.lastname}`
      } else {
        this.selectedMember = null
        this.searchQuery = ''
      }
    }
  },
  methods: {
    onSearchInput() {
      if (this.debounceTimer) {
        clearTimeout(this.debounceTimer)
      }
      
      if (!this.searchQuery || this.searchQuery.length < 2) {
        this.searchResults = []
        this.selectedMember = null
        return
      }

      this.debounceTimer = setTimeout(() => {
        this.searchMembers()
      }, 300)
    },

    async searchMembers() {
      if (!this.searchQuery || this.searchQuery.length < 2) {
        this.searchResults = []
        return
      }

      this.loading = true
      try {
        const res = await axios.get(generateUrl('/apps/clubsuite-core/members/search'), {
          params: { q: this.searchQuery }
        })
        this.searchResults = Array.isArray(res.data) ? res.data : []
      } catch (e) {
        console.error('Fehler beim Suchen von Mitgliedern:', e)
        this.searchResults = []
      } finally {
        this.loading = false
      }
    },

    selectMember(member) {
      this.selectedMember = member
      this.searchQuery = `${member.firstname} ${member.lastname}`
      this.searchResults = []
      this.showDropdown = false
      this.$emit('selected', member)
      this.$emit('input', member)
    },

    clearSelection() {
      this.selectedMember = null
      this.searchQuery = ''
      this.searchResults = []
      this.$emit('selected', null)
      this.$emit('input', null)
    },

    closeDropdownDelayed() {
      setTimeout(() => {
        this.showDropdown = false
      }, 200)
    }
  }
}
</script>

<style scoped>
.member-search-container {
  position: relative;
  flex: 1;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input-wrapper input {
  width: 100%;
  padding: 8px;
  border: 1px solid var(--color-border);
  border-radius: 3px;
}

.loading-spinner {
  position: absolute;
  right: 10px;
  animation: spin 1s linear infinite;
  font-size: 16px;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.dropdown-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: var(--color-main-background);
  border: 1px solid var(--color-border);
  border-top: none;
  border-radius: 0 0 3px 3px;
  max-height: 300px;
  overflow-y: auto;
  z-index: 100;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.loading-message {
  padding: 10px;
  color: var(--color-text-lighter);
  text-align: center;
}

.results-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.result-item {
  padding: 10px 12px;
  cursor: pointer;
  border-bottom: 1px solid var(--color-border-light);
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: background-color 0.2s;
}

.result-item:last-child {
  border-bottom: none;
}

.result-item:hover {
  background-color: var(--color-background-hover);
}

.member-name {
  font-weight: 500;
  color: var(--color-main-text);
}

.member-info {
  color: var(--color-text-lighter);
  font-size: 12px;
}

.no-results {
  padding: 10px;
  color: var(--color-text-lighter);
  text-align: center;
  font-size: 13px;
}

.selected-member {
  margin-top: 8px;
  padding: 8px 10px;
  background: rgba(76, 175, 80, 0.1);
  border: 1px solid rgba(76, 175, 80, 0.3);
  border-radius: 3px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.selected-name {
  color: #4CAF50;
  font-weight: 500;
  font-size: 14px;
}

.clear-btn {
  background: none;
  border: none;
  color: #F44336;
  cursor: pointer;
  font-size: 16px;
  padding: 0;
  margin-left: 10px;
}

.clear-btn:hover {
  opacity: 0.8;
}
</style>
