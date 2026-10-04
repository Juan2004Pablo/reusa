/** «Laureles, cerca al estadio» → «Laureles» (el barrio va primero en la ubicación). */
export function neighborhood(location: string): string {
    return location.split(',')[0].trim() || location;
}
