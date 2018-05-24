<div class="schedule-publication" id="schedulePublication">
  <form action="<?= getPublishDocumentBaseLink($request, $cmsPrefix) ?>">
    <h2>Schedule Publication</h2>
    <p>Please select a date and time for publication</p>
    <input type="hidden" id="schedulePublicationSlug" name="slug"/>
    <input placeholder="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" type="date" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" oninvalid="this.setCustomValidity('Please fill in a date. Use this format: YYYY-MM-DD.');"
           onchange="this.setCustomValidity('');" name="date" required/>
    <input type="time" value="<?= date('H:i') ?>" pattern="(0[0-9]|1[0-9]|2[0-3])(:[0-5][0-9])" oninvalid="this.setCustomValidity('Please fill in a time. Use this format: HH:MM (24 hour notation).');"
           onchange="this.setCustomValidity('');" placeholder="<?= date('H:i') ?>" name="time" required/>
    <button type="submit" class="btn">Schedule Publication</button>
    <a class="btn reset" onclick="toggleSchedulePublicationModal();">Cancel</a>
  </form>
</div>