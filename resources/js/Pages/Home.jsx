import React from 'react'
import { Head , usePage} from '@inertiajs/react'

export default function Home() {
    const { props } = usePage();
    console.log(props);
  return (
    <div>Home</div>
  )
}
