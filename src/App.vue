<template>
  <div id="content" class="app-content">
    <div class="app-navigation">
        <ul class="with-icon">
            <li :class="{ active: view === 'events' }">
                <a href="#" @click.prevent="view = 'events'">
                    <span class="icon-params"><CalendarClockIcon :size="20" /></span>
                    Termine
                </a>
            </li>
            <li :class="{ active: view === 'attendance' }">
                <a href="#" @click.prevent="view = 'attendance'">
                    <span class="icon-params"><ClipboardCheckIcon :size="20" /></span>
                    Anwesenheit
                </a>
            </li>
            <li :class="{ active: view === 'groups' }">
                <a href="#" @click.prevent="view = 'groups'">
                    <span class="icon-params"><AccountGroupIcon :size="20" /></span>
                    Gruppen
                </a>
            </li>
        </ul>
    </div>

    <div id="app-content">
        <div v-if="view === 'events'" class="view-container">
            <div class="header">
                 <h2>Trainingstermine</h2>
                 <button class="primary" @click="showCreate = true">Neuer Termin</button>
            </div>
             
             <div v-if="showCreate" class="create-form">
                <h3>Neuer Termin</h3>
                <form @submit.prevent="createEvent">
                    <div class="form-group">
                        <label>Titel</label>
                        <input v-model="newEvent.title" class="form-control" placeholder="z.B. Jugendprobe" required>
                    </div>
                    <div class="form-group">
                        <label>Datum</label>
                        <input type="date" v-model="newEvent.date" class="form-control" required>
                    </div>
                    <div class="form-group-row">
                        <div class="form-group">
                            <label>Beginn</label>
                            <input type="time" v-model="newEvent.start_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Ende</label>
                            <input type="time" v-model="newEvent.end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Ort</label>
                        <input v-model="newEvent.location" class="form-control" placeholder="z.B. Sporthalle">
                    </div>
                    <div class="form-group">
                        <label>Notizen</label>
                        <input v-model="newEvent.notes" class="form-control">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="primary">Speichern</button>
                        <button type="button" @click="showCreate=false">Abbrechen</button>
                    </div>
                </form>
             </div>

             <table class="grid-table">
                <thead><tr><th>Titel</th><th>Datum</th><th>Zeit</th><th>Ort</th><th>Aktionen</th></tr></thead>
                <tbody>
                    <tr v-for="e in events" :key="e.id">
                        <td>{{ e.title || '-' }}</td>
                        <td>{{ e.date }}</td>
                        <td>{{ e.start_time }} - {{ e.end_time }}</td>
                        <td>{{ e.location || '-' }}</td>
                        <td><button @click="deleteEvent(e.id)">Löschen</button></td>
                    </tr>
                    <tr v-if="events.length === 0"><td colspan="5">Keine Termine</td></tr>
                </tbody>
             </table>
        </div>

        <div v-if="view === 'attendance'" class="view-container">
            <div class="header">
                <h2>Anwesenheitserfassung</h2>
            </div>

            <div class="form-group">
                <label>Termin wählen:</label>
                <select v-model="selectedEventId" class="form-control" @change="loadAttendance">
                    <option value="">-- Termin wählen --</option>
                    <option v-for="e in events" :key="e.id" :value="e.id">{{ e.title || e.date }} ({{ e.start_time }})</option>
                </select>
            </div>

            <div v-if="selectedEventId && selectedEvent" class="event-detail">
                <h3>{{ selectedEvent.title }} - {{ selectedEvent.date }}</h3>
                <p><strong>Zeit:</strong> {{ selectedEvent.start_time }} - {{ selectedEvent.end_time }}</p>
                <p><strong>Ort:</strong> {{ selectedEvent.location || 'Nicht angegeben' }}</p>

                <div class="attendance-section">
                    <h4>Teilnehmer ({{ attendanceRecords.length }})</h4>
                    
                    <table class="grid-table">
                        <thead>
                            <tr>
                                <th>Benutzer</th>
                                <th>Status</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="a in attendanceRecords" :key="a.id" :class="'status-' + a.status">
                                <td>{{ a.user_id }}</td>
                                <td>
                                    <span :class="'badge-' + a.status">
                                        {{ statusLabel(a.status) }}
                                    </span>
                                </td>
                                <td>{{ formatTime(a.checked_in_at) }}</td>
                                <td>{{ formatTime(a.checked_out_at) }}</td>
                                <td>
                                    <button v-if="a.status !== 'present'" @click="markPresent(a.id)" class="btn-small">Anwesend</button>
                                    <button v-if="a.status !== 'absent'" @click="markAbsent(a.id)" class="btn-small">Abwesend</button>
                                    <button v-if="a.status !== 'excused'" @click="markExcused(a.id)" class="btn-small">Entschuldigt</button>
                                </td>
                            </tr>
                            <tr v-if="attendanceRecords.length === 0">
                                <td colspan="5">Keine Teilnehmer erfasst</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="add-participant">
                        <h4>Teilnehmer hinzufügen</h4>
                        <div class="form-group-row">
                            <member-search
                                v-model="selectedMember"
                                @selected="onMemberSelected"
                            />
                            <button 
                                @click="addAttendance" 
                                class="primary"
                                :disabled="!selectedMember"
                            >
                                Hinzufügen
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="placeholder">
                <p>Wählen Sie einen Termin um die Anwesenheit zu erfassen</p>
            </div>
        </div>
        
        <div v-if="view === 'groups'" class="view-container">
            <h2>Trainingsgruppen</h2>
            <p>Verwaltung der Gruppen und Trainer.</p>
        </div>
    </div>
  </div>
</template>

<script>
import CalendarClockIcon from 'vue-material-design-icons/CalendarClock.vue'
import ClipboardCheckIcon from 'vue-material-design-icons/ClipboardCheck.vue'
import AccountGroupIcon from 'vue-material-design-icons/AccountGroup.vue'
import MemberSearch from './components/MemberSearch.vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
  name: 'TrainingApp',
  components: { CalendarClockIcon, ClipboardCheckIcon, AccountGroupIcon, MemberSearch },
  data() {
      return {
          view: 'events',
          showCreate: false,
          events: [],
          selectedEventId: null,
          attendanceRecords: [],
          newEvent: { 
              group_id: 0,
              title: '',
              date: '', 
              start_time: '', 
              end_time: '',
              location: '',
              notes: ''
          },
          newAttendance: {
              user_id: '',
              member_id: null
          },
          selectedMember: null
      }
  },
  mounted() {
      this.loadEvents()
  },
  computed: {
      selectedEvent() {
          if (!this.events || !Array.isArray(this.events)) {
              return null
          }
          if (!this.selectedEventId) {
              return null
          }
          return this.events.find(e => e.id === this.selectedEventId) || null
      }
  },
  methods: {
      async loadEvents() {
          try {
              const res = await axios.get(generateUrl('/apps/clubsuite-training/events'))
              this.events = res.data || []
              // Automatisch erstes Event auswählen, wenn noch keine Auswahl existiert
              if (this.events.length > 0 && !this.selectedEventId) {
                  this.selectedEventId = this.events[0].id
              }
          } catch(e) { 
              console.error('Fehler beim Laden der Events:', e)
              this.events = []
          }
      },
      async createEvent() {
          try {
              // Simple validation or defaults
              if(!this.newEvent.date) this.newEvent.date = new Date().toISOString().slice(0,10);
              
              const res = await axios.post(generateUrl('/apps/clubsuite-training/events'), this.newEvent)
              if(res.status === 201) {
                  this.showCreate = false
                  this.loadEvents()
                  // Reset form
                  this.newEvent = { group_id: 0, title: '', date: '', start_time: '', end_time: '', location: '', notes: '' }
              }
          } catch(e) { console.error(e) }
      },
      async deleteEvent(id) {
           if(!confirm('Löschen?')) return;
           // Implementation stub
      },
      async loadAttendance() {
          if (!this.selectedEventId) {
              this.attendanceRecords = []
              return
          }
          try {
              const res = await axios.get(generateUrl('/apps/clubsuite-training/attendance'), {
                  params: { event_id: this.selectedEventId }
              })
              this.attendanceRecords = Array.isArray(res.data) ? res.data : []
          } catch(e) { 
              console.error('Fehler beim Laden der Anwesenheit:', e)
              this.attendanceRecords = []
          }
      },
      onMemberSelected(member) {
          if (member) {
              this.newAttendance.member_id = member.id
              this.newAttendance.user_id = member.id || `member-${member.id}`
          } else {
              this.newAttendance.member_id = null
              this.newAttendance.user_id = ''
          }
      },
      async addAttendance() {
          if (!this.selectedMember) {
              alert('Bitte wählen Sie ein Mitglied aus')
              return
          }
          try {
              const res = await axios.post(generateUrl('/apps/clubsuite-training/attendance'), {
                  event_id: this.selectedEventId,
                  user_id: this.newAttendance.user_id,
                  member_id: this.newAttendance.member_id
              })
              if(res.status === 201) {
                  this.loadAttendance()
                  this.selectedMember = null
                  this.newAttendance = { user_id: '', member_id: null }
              }
          } catch(e) { 
              console.error('Fehler beim Hinzufügen des Teilnehmers:', e)
              alert('Fehler beim Hinzufügen des Teilnehmers')
          }
      },
      async markPresent(id) {
          try {
              await axios.put(generateUrl('/apps/clubsuite-training/attendance/' + id), {
                  status: 'present'
              })
              this.loadAttendance()
          } catch(e) { 
              console.error('Fehler beim Markieren als anwesend:', e)
          }
      },
      async markAbsent(id) {
          try {
              await axios.put(generateUrl('/apps/clubsuite-training/attendance/' + id), {
                  status: 'absent'
              })
              this.loadAttendance()
          } catch(e) { 
              console.error('Fehler beim Markieren als abwesend:', e)
          }
      },
      async markExcused(id) {
          try {
              await axios.put(generateUrl('/apps/clubsuite-training/attendance/' + id), {
                  status: 'excused'
              })
              this.loadAttendance()
          } catch(e) { 
              console.error('Fehler beim Markieren als entschuldigt:', e)
          }
      },
      statusLabel(status) {
          const labels = {
              'present': 'Anwesend',
              'absent': 'Abwesend',
              'excused': 'Entschuldigt'
          }
          return labels[status] || status
      },
      formatTime(dt) {
          if (!dt) return '-'
          try {
              const d = new Date(dt)
              return d.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })
          } catch(e) {
              return '-'
          }
      }
  }
}
</script>

<style scoped>
.app-content { display: flex; height: 100vh; overflow: hidden; }
.app-navigation { width: 300px; border-right: 1px solid var(--color-border); padding-top: 10px; background: var(--color-main-background); }
#app-content { flex-grow: 1; padding: 20px; overflow-y: auto; }
.with-icon { list-style: none; padding: 0; margin: 0; }
.with-icon a { display: flex; align-items: center; padding: 10px 15px; text-decoration: none; color: var(--color-main-text); opacity: 0.7; }
.with-icon li.active a { opacity: 1; font-weight: bold; background: var(--color-background-hover); }
.icon-params { margin-right: 10px; display: flex; }

.view-container { margin-bottom: 20px; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid var(--color-border); }
.create-form { background: var(--color-background-hover); padding: 15px; border-radius: 5px; margin-bottom: 20px; }
.form-group { margin-bottom: 10px; }
.form-group-row { display: flex; gap: 10px; }
.form-control { flex: 1; padding: 8px; border: 1px solid var(--color-border); border-radius: 3px; }

.grid-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
.grid-table th, .grid-table td { padding: 10px; border-bottom: 1px solid var(--color-border); text-align: left; }
.grid-table th { background: var(--color-background-hover); font-weight: bold; }
.grid-table tr.status-present { background: rgba(76, 175, 80, 0.1); }
.grid-table tr.status-absent { background: rgba(244, 67, 54, 0.1); }
.grid-table tr.status-excused { background: rgba(255, 193, 7, 0.1); }

.badge-present { background: #4CAF50; color: white; padding: 3px 8px; border-radius: 3px; font-weight: bold; }
.badge-absent { background: #F44336; color: white; padding: 3px 8px; border-radius: 3px; font-weight: bold; }
.badge-excused { background: #FFC107; color: black; padding: 3px 8px; border-radius: 3px; font-weight: bold; }

.event-detail { background: var(--color-background-hover); padding: 15px; border-radius: 5px; margin-bottom: 20px; }
.event-detail h3 { margin-top: 0; }
.event-detail p { margin: 5px 0; }

.attendance-section { margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--color-border); }
.attendance-section h4 { margin-top: 0; }

.add-participant { background: var(--color-main-background); padding: 15px; border-radius: 5px; margin-top: 15px; border: 1px solid var(--color-border); }
.add-participant h4 { margin-top: 0; margin-bottom: 10px; }

button.primary { background-color: var(--color-primary); color: white; border: none; padding: 8px 12px; border-radius: 3px; cursor: pointer; }
button.primary:disabled { opacity: 0.5; cursor: not-allowed; }
button.btn-small { padding: 4px 8px; font-size: 12px; background: var(--color-background-darker); border: 1px solid var(--color-border); border-radius: 3px; cursor: pointer; }
button { margin-right: 5px; cursor: pointer; }
button:hover { opacity: 0.8; }

.placeholder { text-align: center; padding: 40px; color: var(--color-text-lighter); }
</style>