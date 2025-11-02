
function extractDistrict(displayName) {
  const match = displayName.match(/okres\s+([^\s,]+)/i);
  return match ? match[1] : '';
}

export async function geocodeAddress(address) {
  if (!address) return { coordinates: '', district: '' };

  try {
    const encoded = encodeURIComponent(address);
    const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encoded}`;
    const res = await fetch(url, { headers: { 'Accept-Language': 'sk' } });
    const data = await res.json();

    if (data.length > 0) {
      const place = data[0];
      const lat = place.lat;
      const lon = place.lon;
      const district = extractDistrict(place.display_name);
      return { coordinates: `${lat},${lon}`, district };
    } else {
      return { coordinates: '', district: '' };
    }
  } catch (err) {
    console.error('Geokódovanie zlyhalo', err);
    return { coordinates: '', district: '' };
  }
}
