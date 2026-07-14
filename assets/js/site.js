'use strict';

(() => {
  const contactSubmit = $('#contact-submit');
  const contactForm = $('#contact-form');
  const messageResult = $('#message-result p');
  
  function callServer(scriptName, data, successHandler) {
    const url = `./scripts/${scriptName}`;
    $.ajax({
      url: url,
      type: 'POST',
      dataType: 'json',
      processData: false,
      contentType: false,
      data: data,
      success: (response) => {
        if (response.status === 'ok') {
          successHandler?.(response.data);
        } else {
          console.error(response);
        }
      },
      error: function (jqXHR) {
        console.error('AJAX Failed: ', jqXHR.statusText);
      }
    });
  }
  
//
  
  function buildAttr(list) {
    let ret = '';
    if (list && typeof list === 'object') {
      for (let lp = 0; lp < list.length; lp++) {
        let entry = list[lp];
        if (typeof entry === 'object') {
          for (const [key, value] of Object.entries(entry)) {
            const item = key + '="' + value + '"';
            ret += ` ${item}`;
          }
        } else {
          ret += entry;
        }
      }
    } else {
      ret = list;
    }
    return ret;
  }
  
  function buildTag(tag, className = null, attrList = null, content = '', autoClose = false) {
    const classAttr = className ? ` class="${className}"` : '';
    const attrs = attrList ? buildAttr(attrList) : '';
    if (autoClose) {
      return `<${tag}${classAttr}${attrs} />`;
    }
    const body = Array.isArray(content)
      ? content.join('')
      : content;
    return `<${tag}${classAttr}${attrs}>${body}</${tag}>`;
  }
  
  function getDateFormats(value) {
    let ret = {};
    if (value) {
      const parts = value.split('-'),
        date = new Date(parts[0], parts[1] - 1, parts[2]),
        dft = new Intl.DateTimeFormat('en-GB', {
          dateStyle: 'full'
        });
      ret['full'] = dft.format(date);
      ret['day'] = date.getDate();
      ret['month'] = date.toLocaleString('en-GB', {month: 'short'});
    }
    return ret;
  }
  
  function buildDescription(desc) {
    return (desc)
      ? buildTag('p', 'event', null, desc)
      : '';
  }
  
  const buildVenue = name => name ? buildTag('h2', 'title', null, name) : '';
  
  const buildAddress = addr =>
    addr ? buildTag('div', 'loc', null, [
      buildTag('div', 'icon', null,
        buildTag('i', 'fa-solid fa-map')
      ),
      buildTag('p', null, null, addr)
    ]) : '';
  
  const buildBookingURL = url =>
    url ? buildTag('a', 'tickets', [{href: url, target: '_blank'}], 'Book Now') : '';
  
  const getDateRange = (startDate, endDate) =>
    (endDate && startDate !== endDate)
      ? buildTag('span', null, null, startDate) + buildTag('span', null, null, '&nbsp;until&nbsp;') + buildTag('span', null, null, endDate)
      : buildTag('span', null, null, startDate);
  
  function formatDateTime(startDate, endDate, startTime) {
    const ret = (startTime)
      ? buildTag('span', null, null, startTime)
      : getDateRange(startDate, endDate);
    return buildTag('div', 'sce', null, [
      buildTag('div', 'icon', null,
        buildTag('i', 'fa-solid fa-clock')
      ), ret
    ]);
  }
  
  function buildEventEntry(element, value) {
    const startDate = getDateFormats(value.startDate),
      endDate = getDateFormats(value.endDate),
      eventDate = formatDateTime(startDate.full, endDate.full, value.startTime),
      eventDay = startDate.day + '',
      eventMonth = startDate.month;
    const leftEle =
      buildTag('div', 'item-left', null, [
        buildTag('h2', 'num', null, eventDay),
        buildTag('p', 'day', null, eventMonth),
        buildTag('span', 'up-border'),
        buildTag('span', 'down-border')
      ]);
    const rightEle = buildTag(
      'div', 'item-right', null, [
        buildDescription(value.description),
        buildVenue(value.venueName),
        eventDate,
        buildTag('div', 'fix'),
        buildAddress(value.venueAddress),
        buildTag('div', 'fix'),
        buildBookingURL(value.bookingURL)
      ]
    );
    element.append(
      buildTag('div', 'item', null, [
        leftEle, rightEle
      ])
    );
  }
  
  function renderFutureEvents(data) {
    const element = $('#future-events');
    for (const [key, value] of Object.entries(data)) {
      buildEventEntry(element, value);
    }
  }
  
  function messageHandler(e) {
    messageResult.html('<strong>' + e.message + '</strong>');
    if (e.status === 'ok') {
      messageResult.addClass('text-success');
      messageResult.removeClass('text-danger');
    } else {
      messageResult.addClass('text-danger');
      messageResult.removeClass('text-success');
    }
    contactSubmit.removeAttr('disabled', 'disabled');
    contactSubmit.html('Send Message');
  }
  
  function submitClickHandler() {
    contactForm.off('submit');
    contactForm.submit((e) => {
      e.preventDefault();
      const formData = new FormData(document.getElementById('contact-form'));
      contactSubmit.attr('disabled', 'disabled');
      contactSubmit.html('Sending message...');
      callServer('Control/ProcessMessage.php', formData, messageHandler);
    });
  }

//
  
  callServer(
    'Control/BuilderEvents.php', null,
    (e) => {
      console.log('found ', e);
      if (e) {
        renderFutureEvents(e.future);
      }
    }
  );
  
  messageResult.html('');
  submitClickHandler();
  
  const cookieBanner = document.getElementById('cookie-banner');
  
  const showCookieBanner = () => {
    cookieBanner.style.display = 'block';
  };
  
  const hideCookieBanner = () => {
    localStorage.setItem('isCookieAccepted', 'yes');
    cookieBanner.style.display = 'none';
  };
  
  const initializeCookieBanner = () => {
    if (localStorage.getItem('isCookieAccepted') !== 'yes') {
      showCookieBanner();
    }
  };
  
  if (cookieBanner) {
    initializeCookieBanner();
  }
})();

//
