<style>
.consent-banner {
  --blue-d: rgb(9,58,126);
  --blue-m: rgb(62,178,240);
  --text:   rgb(12,22,42);
  --muted:  rgb(72,87,112);
  font-family: 'Space Grotesk', sans-serif;
  -webkit-font-smoothing: antialiased;
  position: fixed;
  left: 0; right: 0; bottom: 0;
  z-index: 9500;
  background: #fff;
  border-top: 1px solid rgba(9,58,126,0.1);
  box-shadow: 0 -8px 40px rgba(0,0,0,0.08);
  padding: 1.3rem 1.5rem;
  transform: translateY(100%);
  opacity: 0;
  transition: transform 0.4s cubic-bezier(0.23,1,0.32,1), opacity 0.3s ease;
  pointer-events: none;
}
.consent-banner--visible {
  transform: translateY(0);
  opacity: 1;
  pointer-events: auto;
}
.consent-banner__inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 2rem;
  flex-wrap: wrap;
}
.consent-banner__text {
  font-size: 0.82rem;
  line-height: 1.55;
  color: var(--muted);
  max-width: 640px;
}
.consent-banner__text a {
  color: var(--blue-d);
  text-decoration: underline;
}
.consent-banner__actions {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
  flex-shrink: 0;
}
.consent-banner__btn {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  padding: 0.65rem 1.15rem;
  border: 1px solid transparent;
  transition: opacity 0.2s, transform 0.15s, border-color 0.2s;
}
.consent-banner__btn:hover { transform: translateY(-1px); }
.consent-banner__btn--primary {
  color: #fff;
  background: linear-gradient(90deg, var(--blue-d), var(--blue-m));
  box-shadow: 0 4px 18px rgba(9,58,126,0.2);
}
.consent-banner__btn--primary:hover { opacity: 0.9; }
.consent-banner__btn--ghost {
  background: transparent;
  border-color: rgba(9,58,126,0.2);
  color: var(--blue-d);
}
.consent-banner__btn--ghost:hover { border-color: rgba(9,58,126,0.4); }
.consent-banner__btn--text {
  background: none;
  border: none;
  color: var(--muted);
  text-decoration: underline;
  padding: 0.65rem 0.2rem;
}
.consent-banner__btn--text:hover { color: var(--text); }

.consent-banner__preferences {
  max-width: 1200px;
  margin: 0 auto;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.35s cubic-bezier(0.23,1,0.32,1);
}
.consent-banner__preferences--open {
  max-height: 400px;
  margin-top: 1.1rem;
  padding-top: 1.1rem;
  border-top: 1px solid rgba(9,58,126,0.07);
}
.consent-banner__category {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1.5rem;
  padding: 0.6rem 0;
}
.consent-banner__category-label {
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 0.15rem;
}
.consent-banner__category-desc {
  font-size: 0.72rem;
  color: var(--muted);
  line-height: 1.5;
}
.consent-banner__category input[type="checkbox"] {
  margin-top: 0.2rem;
  flex-shrink: 0;
  width: 18px;
  height: 18px;
  accent-color: var(--blue-d);
}
.consent-banner__save {
  margin-top: 0.8rem;
}

@media (max-width: 760px) {
  .consent-banner { padding: 1.1rem 1.2rem; }
  .consent-banner__inner { flex-direction: column; align-items: stretch; gap: 1rem; }
  .consent-banner__actions { justify-content: flex-start; }
}
</style>

<div class="consent-banner" id="consent-banner" role="dialog" aria-live="polite" aria-label="Cookie-Einstellungen">
  <div class="consent-banner__inner">
    <p class="consent-banner__text">
      Wir verwenden Cookies, um unsere Website zu betreiben. Optionale Cookies (z.&nbsp;B. für Analyse) setzen wir nur mit Ihrer Zustimmung. Mehr dazu in unserer <a href="/datenschutz">Datenschutzerklärung</a>.
    </p>
    <div class="consent-banner__actions">
      <button type="button" class="consent-banner__btn consent-banner__btn--text" data-consent-toggle-preferences>Einstellungen</button>
      <button type="button" class="consent-banner__btn consent-banner__btn--ghost" data-consent-accept-necessary>Nur notwendige</button>
      <button type="button" class="consent-banner__btn consent-banner__btn--primary" data-consent-accept-all>Alle akzeptieren</button>
    </div>
  </div>

  <div class="consent-banner__preferences" data-consent-preferences>
    <div class="consent-banner__category">
      <div>
        <div class="consent-banner__category-label">Notwendig</div>
        <div class="consent-banner__category-desc">Erforderlich, damit die Website grundlegend funktioniert. Kann nicht deaktiviert werden.</div>
      </div>
      <input type="checkbox" checked disabled aria-label="Notwendig (immer aktiv)"/>
    </div>
    <div class="consent-banner__category">
      <div>
        <div class="consent-banner__category-label">Analyse</div>
        <div class="consent-banner__category-desc">Hilft uns zu verstehen, wie die Website genutzt wird.</div>
      </div>
      <input type="checkbox" data-consent-category="analytics" aria-label="Analyse-Cookies"/>
    </div>
    <div class="consent-banner__category">
      <div>
        <div class="consent-banner__category-label">Marketing</div>
        <div class="consent-banner__category-desc">Für personalisierte Werbung und Kampagnenmessung.</div>
      </div>
      <input type="checkbox" data-consent-category="marketing" aria-label="Marketing-Cookies"/>
    </div>
    <div class="consent-banner__save">
      <button type="button" class="consent-banner__btn consent-banner__btn--primary" data-consent-save-preferences>Auswahl speichern</button>
    </div>
  </div>
</div>
