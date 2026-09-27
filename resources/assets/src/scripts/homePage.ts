import { getExtraData } from './extra'

export function scrollHander() {
  const header = document.querySelector('.navbar')
  const hero = document.querySelector('.hp-wrapper')
  /* istanbul ignore else */
  if (header) {
    window.addEventListener('scroll', () => {
      const threshold = hero ? Math.max(hero.clientHeight - 80, 50) : 100
      if (window.scrollY >= threshold) {
        header.classList.remove('transparent')
      } else {
        header.classList.add('transparent')
      }
    })
  }
}

/* istanbul ignore next */
if (process.env.NODE_ENV !== 'test') {
  const { transparent_navbar } = getExtraData() as {
    transparent_navbar: boolean
  }
  if (transparent_navbar) {
    window.addEventListener('load', scrollHander)
  }
}
